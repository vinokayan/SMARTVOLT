<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MQTTService;
use App\Models\Device;
use App\Models\EnergyLog;

class MQTTListen extends Command
{
    protected $signature   = 'mqtt:listen';
    protected $description = 'Listen MQTT dari ESP32 dan simpan data ke database';

    public function handle()
    {
        $this->info('SmartVolt MQTT Listener berjalan...');
        $this->info('Mendengarkan topic: energy/#');
        $this->line('------------------------------------------------------');

        $mqtt = new MQTTService();

        // ── Subscribe topic data sensor ────────────────────────────────
        // Topic  : energy/{esp32_device_id}
        // Payload: {"v":220.5,"i":1.80,"p":396.9,"e":0.011,"f":50.0,"pf":0.99}
        $mqtt->subscribe('energy/#', function ($topic, $message) {

            // Ambil esp32_device_id dari topic
            $parts   = explode('/', $topic);
            $esp32Id = $parts[1] ?? null;

            if (!$esp32Id) {
                $this->warn("[!] Topic tidak valid: {$topic}");
                return;
            }

            // Parse JSON
            $data = json_decode($message, true);

            if (!$data || !is_array($data)) {
                $this->warn("[!] Format JSON salah dari {$esp32Id}: {$message}");
                $this->line("    Contoh format: {\"v\":220.5,\"i\":1.80,\"p\":396.9,\"e\":0.011}");
                return;
            }

            // Cari device berdasarkan esp32_device_id
            $device = Device::where('esp32_device_id', $esp32Id)->first();

            if (!$device) {
                $this->warn("[!] Device '{$esp32Id}' belum terdaftar di SmartVolt.");
                $this->line("    Daftarkan lewat halaman Devices, isi kolom ESP32 Device ID.");
                return;
            }

            // Simpan ke database
            EnergyLog::create([
                'device_id'    => $device->id,
                'voltage'      => round($data['v']  ?? 0,    2),
                'current'      => round($data['i']  ?? 0,    3),
                'power'        => round($data['p']  ?? 0,    2),
                'energy_kwh'   => round($data['e']  ?? 0,    4),
                'frequency'    => round($data['f']  ?? 50.0, 2),
                'power_factor' => round($data['pf'] ?? 1.0,  3),
            ]);

            // Update status device: ON jika power > 5W
            $isOn = ($data['p'] ?? 0) > 5;
            $device->update(['status' => $isOn]);

            // Log di terminal
            $this->line(sprintf(
                "[%s] %-20s | V:%-6s I:%-5s P:%-7s kWh:%-8s | %s",
                now()->format('H:i:s'),
                $device->name,
                ($data['v'] ?? 0) . 'V',
                ($data['i'] ?? 0) . 'A',
                ($data['p'] ?? 0) . 'W',
                ($data['e'] ?? 0),
                $isOn ? '● ON' : '○ OFF'
            ));
        });

        // ── Mulai loop — HARUS dipanggil setelah semua subscribe ──────
        // Loop ini berjalan terus-menerus menunggu pesan masuk
        $mqtt->loop();
    }
}