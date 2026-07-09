<?php

namespace Tests\Feature\SmartVolt;

use App\Models\Device;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SmartVoltRelayCommandApiTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey = 'sv_test_key';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.iot.api_key' => $this->apiKey,
        ]);
    }

    private function createUser(): User
    {
        return User::forceCreate([
            'name' => 'SmartVolt Relay Tester',
            'email' => 'smartvolt-relay@example.test',
            'password' => Hash::make('password'),
        ]);
    }

    private function createRoom(User $user): Room
    {
        return Room::forceCreate([
            'user_id' => $user->id,
            'name' => 'Kamar Relay Test',
        ]);
    }

    private function createRelay(Room $room, array $overrides = []): Device
    {
        foreach (['relay_code', 'esp_unit_id', 'esp32_device_id'] as $column) {
            $this->assertTrue(
                Schema::hasColumn('devices', $column),
                "Kolom devices.{$column} belum ada. Test command relay membutuhkan kolom ini."
            );
        }

        $attributes = [
            'room_id' => $room->id,
            'name' => 'Relay Test',
            'relay_code' => '1',
            'esp_unit_id' => '2',
            'esp32_device_id' => '2',
            'status' => false,
        ];

        if (Schema::hasColumn('devices', 'user_id')) {
            $attributes['user_id'] = $room->user_id;
        }

        if (Schema::hasColumn('devices', 'device_key')) {
            $attributes['device_key'] = null;
        }

        return Device::forceCreate(array_merge($attributes, $overrides));
    }

    public function test_commands_requires_valid_api_key(): void
    {
        $response = $this->getJson('/api/iot/esp/2/commands');

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid API Key.',
        ]);
    }

    public function test_commands_returns_all_relays_for_esp_unit(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom($user);

        $relay1 = $this->createRelay($room, [
            'name' => 'Lampu Test',
            'relay_code' => '1',
            'status' => false,
        ]);

        $relay2 = $this->createRelay($room, [
            'name' => 'Kipas Test',
            'relay_code' => '2',
            'status' => true,
        ]);

        $response = $this
            ->withHeader('X-API-KEY', $this->apiKey)
            ->getJson('/api/iot/esp/2/commands');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'esp_unit_id' => '2',
            'esp32_device_id' => '2',
            'refresh_interval' => 5,
            'total_relays' => 2,
        ]);

        $response->assertJsonPath('relays.0.device_id', $relay1->id);
        $response->assertJsonPath('relays.0.device_name', 'Lampu Test');
        $response->assertJsonPath('relays.0.relay_code', '1');
        $response->assertJsonPath('relays.0.relay', false);
        $response->assertJsonPath('relays.0.status', 'OFF');

        $response->assertJsonPath('relays.1.device_id', $relay2->id);
        $response->assertJsonPath('relays.1.device_name', 'Kipas Test');
        $response->assertJsonPath('relays.1.relay_code', '2');
        $response->assertJsonPath('relays.1.relay', true);
        $response->assertJsonPath('relays.1.status', 'ON');
    }

    public function test_commands_returns_404_when_esp_has_no_relays(): void
    {
        $response = $this
            ->withHeader('X-API-KEY', $this->apiKey)
            ->getJson('/api/iot/esp/999/commands');

        $response->assertNotFound();
        $response->assertJson([
            'success' => false,
            'message' => 'Tidak ada device untuk ESP32 ini.',
            'esp_unit_id' => '999',
            'esp32_device_id' => '999',
            'refresh_interval' => 5,
            'relays' => [],
        ]);
    }

    public function test_legacy_single_command_endpoint_returns_first_relay(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom($user);

        $relay = $this->createRelay($room, [
            'name' => 'Lampu Legacy Test',
            'relay_code' => '1',
            'status' => true,
        ]);

        $response = $this
            ->withHeader('X-API-KEY', $this->apiKey)
            ->getJson('/api/device/2/command');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'esp_unit_id' => '2',
            'esp32_device_id' => '2',
            'device_id' => $relay->id,
            'device_name' => 'Lampu Legacy Test',
            'relay_code' => '1',
            'relay' => true,
            'status' => 'ON',
            'refresh_interval' => 5,
        ]);
    }

    public function test_legacy_unit_commands_endpoint_still_works(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom($user);

        $this->createRelay($room, [
            'name' => 'Relay Legacy Unit Test',
            'relay_code' => '1',
            'status' => false,
        ]);

        $response = $this
            ->withHeader('X-API-KEY', $this->apiKey)
            ->getJson('/api/unit/2/commands');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'esp_unit_id' => '2',
            'total_relays' => 1,
        ]);

        $response->assertJsonPath('relays.0.relay_code', '1');
        $response->assertJsonPath('relays.0.status', 'OFF');
    }
}