<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MQTTService
{
    protected MqttClient $mqtt;

    public function __construct()
    {
        try {
            $settings = (new ConnectionSettings())
                ->setKeepAliveInterval(60)
                ->setConnectTimeout(10);

            $this->mqtt = new MqttClient(
                '127.0.0.1',
                1883,
                'smartvolt-laravel-' . uniqid()
            );

            $this->mqtt->connect($settings, true);
            echo "MQTT Connected\n";

        } catch (\Exception $e) {
            echo "MQTT ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    public function subscribe(string $topic, callable $callback): void
    {
        $this->mqtt->subscribe($topic, $callback, 0);
    }

    // Jalankan loop tanpa batas — panggil SETELAH semua subscribe
    public function loop(): void
    {
        $this->mqtt->loop(true, true);
    }

    public function publish(string $topic, string $message): void
    {
        $this->mqtt->publish($topic, $message, 0);
    }

    public function disconnect(): void
    {
        $this->mqtt->disconnect();
    }
}