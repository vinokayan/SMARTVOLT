<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\EnergyLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;
use Throwable;

class MQTTListen extends Command
{
    protected $signature = 'mqtt:listen';

    protected $description = 'Listen to SmartVolt MQTT energy data';

    public function handle(): int
    {
        $topic = 'smartvolt/energy/+';

        $this->info('SmartVolt MQTT listener started...');
        $this->info('Listening topic: ' . $topic);

        while (true) {
            try {
                $mqtt = MQTT::connection();

                $mqtt->subscribe($topic, function (string $topic, string $message) {
                    $this->processEnergyMessage($topic, $message);
                }, 0);

                $mqtt->loop(true);
            } catch (Throwable $e) {
                $this->error('MQTT connection lost: ' . $e->getMessage());

                Log::error('MQTT listener disconnected', [
                    'error' => $e->getMessage(),
                ]);

                $this->warn('Reconnecting to MQTT broker in 3 seconds...');
                sleep(3);
            } finally {
                try {
                    if (isset($mqtt)) {
                        $mqtt->disconnect();
                    }
                } catch (Throwable $e) {
                    // Ignore disconnect errors.
                }
            }
        }

        return self::SUCCESS;
    }

    private function processEnergyMessage(string $topic, string $message): void
    {
        try {
            $this->info('Message received on ' . $topic . ': ' . $message);

            $payload = json_decode($message, true);

            if (!is_array($payload)) {
                $this->error('Invalid JSON payload');
                return;
            }

            $deviceKey = $payload['esp32_device_id'] ?? $this->getDeviceKeyFromTopic($topic);

            if (!$deviceKey) {
                $this->error('Device key not found in payload or topic.');
                return;
            }

            $device = Device::where('esp32_device_id', (string) $deviceKey)->first();

            if (!$device) {
                $this->error('Device not found: ' . $deviceKey);
                return;
            }

            EnergyLog::create([
                'device_id' => $device->id,
                'voltage' => (float) ($payload['voltage'] ?? 0),
                'current' => (float) ($payload['current'] ?? 0),
                'power' => (float) ($payload['power'] ?? 0),
                'energy' => (float) ($payload['energy'] ?? 0),
                'frequency' => (float) ($payload['frequency'] ?? 0),
                'power_factor' => (float) ($payload['power_factor'] ?? 0),
            ]);

            $this->info('Energy data saved for ' . $deviceKey);
        } catch (Throwable $e) {
            $this->error('Failed to process MQTT message: ' . $e->getMessage());

            Log::error('Failed to process MQTT energy message', [
                'topic' => $topic,
                'message' => $message,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getDeviceKeyFromTopic(string $topic): ?string
    {
        $parts = explode('/', $topic);

        return end($parts) ?: null;
    }
}