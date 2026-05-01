<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\EnergyLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';

    protected $description = 'Listen MQTT energy data from ESP32 and store it to energy_logs';

    public function handle(): int
    {
        $this->info('SmartVolt MQTT listener started...');
        $this->info('Listening topic: smartvolt/energy/+');

        $mqtt = MQTT::connection();

        $mqtt->subscribe('smartvolt/energy/+', function (string $topic, string $message) {
            $this->info("Message received on {$topic}: {$message}");

            $payload = json_decode($message, true);

            if (!is_array($payload)) {
                Log::warning('Invalid MQTT JSON payload', [
                    'topic' => $topic,
                    'message' => $message,
                ]);

                $this->warn('Invalid JSON payload');
                return;
            }

            $topicParts = explode('/', $topic);
            $esp32DeviceId = $payload['esp32_device_id'] ?? end($topicParts);

            $device = Device::where('esp32_device_id', $esp32DeviceId)->first();

            if (!$device) {
                Log::warning('MQTT device not found', [
                    'esp32_device_id' => $esp32DeviceId,
                    'topic' => $topic,
                ]);

                $this->warn("Device not found: {$esp32DeviceId}");
                return;
            }

            EnergyLog::create([
                'device_id' => $device->id,
                'voltage' => $payload['voltage'] ?? 0,
                'current' => $payload['current'] ?? 0,
                'power' => $payload['power'] ?? 0,
                'energy' => $payload['energy'] ?? 0,
                'frequency' => $payload['frequency'] ?? null,
                'power_factor' => $payload['power_factor'] ?? null,
            ]);

            $this->info("Energy data saved for {$esp32DeviceId}");
        }, 0);

        $mqtt->loop(true);

        return self::SUCCESS;
    }
}