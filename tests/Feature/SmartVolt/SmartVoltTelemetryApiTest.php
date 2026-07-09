<?php

namespace Tests\Feature\SmartVolt;

use App\Models\EnergyLog;
use App\Models\EnergyMeter;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SmartVoltTelemetryApiTest extends TestCase
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
            'name' => 'SmartVolt Tester',
            'email' => 'smartvolt-tester@example.test',
            'password' => Hash::make('password'),
        ]);
    }

    private function createRoom(User $user): Room
    {
        return Room::forceCreate([
            'user_id' => $user->id,
            'name' => 'Dapur Test',
        ]);
    }

    private function createEnergyMeter(User $user, Room $room, array $overrides = []): EnergyMeter
    {
        return EnergyMeter::forceCreate(array_merge([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'esp_unit_id' => '2',
            'meter_code' => 'main',
            'name' => 'Sensor Listrik Dapur Test',
            'sensor_type' => 'PZEM004T',
            'is_active' => true,
        ], $overrides));
    }

    private function validTelemetryPayload(array $overrides = []): array
    {
        return array_merge([
            'esp_unit_id' => '2',
            'meter_code' => 'main',
            'telemetry_id' => 'telemetry-test-001',
            'observed_at' => '2026-07-08T10:00:00+07:00',
            'voltage' => 220.5,
            'current' => 0.82,
            'power' => 180.25,
            'energy' => 1.2345,
            'frequency' => 50.0,
            'power_factor' => 0.95,
        ], $overrides);
    }

    public function test_telemetry_requires_valid_api_key(): void
    {
        $response = $this->postJson('/api/iot/telemetry', $this->validTelemetryPayload());

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid API Key.',
        ]);
    }

    public function test_telemetry_rejects_invalid_api_key(): void
    {
        $response = $this
            ->withHeader('X-API-KEY', 'wrong-key')
            ->postJson('/api/iot/telemetry', $this->validTelemetryPayload());

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid API Key.',
        ]);
    }

    public function test_telemetry_creates_energy_log_for_registered_active_meter(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom($user);
        $meter = $this->createEnergyMeter($user, $room);

        $response = $this
            ->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/api/iot/telemetry', $this->validTelemetryPayload());

        $response->assertCreated();
        $response->assertJson([
            'success' => true,
            'esp_unit_id' => '2',
            'meter_code' => 'main',
            'energy_meter_id' => $meter->id,
            'created_logs' => 1,
            'duplicate_logs' => 0,
        ]);

        $this->assertDatabaseCount('energy_logs', 1);

        $this->assertDatabaseHas('energy_logs', [
            'device_id' => null,
            'energy_meter_id' => $meter->id,
            'telemetry_id' => 'telemetry-test-001',
        ]);

        $log = EnergyLog::first();

        $this->assertNotNull($log);
        $this->assertSame($meter->id, $log->energy_meter_id);
        $this->assertNull($log->device_id);
        $this->assertEquals(220.5, $log->voltage);
        $this->assertEquals(0.82, $log->current);
        $this->assertEquals(180.25, $log->power);
        $this->assertEquals(1.2345, $log->energy);
        $this->assertEquals(50.0, $log->frequency);
        $this->assertEquals(0.95, $log->power_factor);
    }

    public function test_telemetry_uses_main_meter_code_when_meter_code_is_not_sent(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom($user);
        $meter = $this->createEnergyMeter($user, $room, [
            'meter_code' => 'main',
        ]);

        $payload = $this->validTelemetryPayload([
            'telemetry_id' => 'telemetry-no-meter-code',
        ]);

        unset($payload['meter_code']);

        $response = $this
            ->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/api/iot/telemetry', $payload);

        $response->assertCreated();
        $response->assertJson([
            'success' => true,
            'meter_code' => 'main',
            'energy_meter_id' => $meter->id,
        ]);

        $this->assertDatabaseHas('energy_logs', [
            'energy_meter_id' => $meter->id,
            'telemetry_id' => 'telemetry-no-meter-code',
        ]);
    }

    public function test_telemetry_accepts_legacy_esp32_device_id_field(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom($user);
        $meter = $this->createEnergyMeter($user, $room);

        $payload = $this->validTelemetryPayload([
            'telemetry_id' => 'legacy-esp32-id-001',
        ]);

        unset($payload['esp_unit_id']);

        $payload['esp32_device_id'] = '2';

        $response = $this
            ->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/api/iot/telemetry', $payload);

        $response->assertCreated();
        $response->assertJson([
            'success' => true,
            'esp_unit_id' => '2',
            'energy_meter_id' => $meter->id,
        ]);

        $this->assertDatabaseHas('energy_logs', [
            'energy_meter_id' => $meter->id,
            'telemetry_id' => 'legacy-esp32-id-001',
        ]);
    }

    public function test_duplicate_telemetry_id_does_not_create_duplicate_log(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom($user);
        $meter = $this->createEnergyMeter($user, $room);

        $payload = $this->validTelemetryPayload([
            'telemetry_id' => 'duplicate-telemetry-001',
        ]);

        $firstResponse = $this
            ->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/api/iot/telemetry', $payload);

        $secondResponse = $this
            ->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/api/iot/telemetry', $payload);

        $firstResponse->assertCreated();

        $secondResponse->assertOk();
        $secondResponse->assertJson([
            'success' => true,
            'created_logs' => 0,
            'duplicate_logs' => 1,
        ]);

        $this->assertDatabaseCount('energy_logs', 1);

        $this->assertDatabaseHas('energy_logs', [
            'energy_meter_id' => $meter->id,
            'telemetry_id' => 'duplicate-telemetry-001',
        ]);
    }

    public function test_telemetry_returns_404_when_meter_is_not_registered(): void
    {
        $response = $this
            ->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/api/iot/telemetry', $this->validTelemetryPayload());

        $response->assertNotFound();
        $response->assertJson([
            'success' => false,
            'message' => 'Energy meter tidak ditemukan atau tidak aktif.',
            'esp_unit_id' => '2',
            'meter_code' => 'main',
        ]);

        $this->assertDatabaseCount('energy_logs', 0);
    }

    public function test_telemetry_returns_404_when_meter_is_inactive(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom($user);

        $this->createEnergyMeter($user, $room, [
            'is_active' => false,
        ]);

        $response = $this
            ->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/api/iot/telemetry', $this->validTelemetryPayload());

        $response->assertNotFound();
        $response->assertJson([
            'success' => false,
            'message' => 'Energy meter tidak ditemukan atau tidak aktif.',
        ]);

        $this->assertDatabaseCount('energy_logs', 0);
    }

    public function test_telemetry_validation_rejects_missing_required_power_data(): void
    {
        $payload = $this->validTelemetryPayload();

        unset($payload['voltage']);

        $response = $this
            ->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/api/iot/telemetry', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['voltage']);
    }
}