<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\EnergyLog;
use App\Models\Room;
use App\Models\SystemSetting;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EnergyApiController extends Controller
{
    /**
     * Mengecek API key dari ESP32.
     */
    private function checkApiKey(Request $request): void
    {
        $configuredApiKey = config('services.iot.api_key');
        $requestApiKey = (string) $request->header('X-API-KEY');

        if (blank($configuredApiKey)) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'IoT API key belum dikonfigurasi di server.',
            ], 500));
        }

        if (! hash_equals((string) $configuredApiKey, $requestApiKey)) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Invalid API Key.',
            ], 401));
        }
    }

    /**
     * Menyimpan / memperbarui data sensor energi dari ESP32.
     *
     * Endpoint:
     * POST /api/energy/store
     *
     * Catatan:
     * - Tidak membuat banyak baris log lagi.
     * - Untuk setiap device_id hanya disimpan 1 baris data sensor terbaru.
     * - Data lama untuk device yang sama akan dihapus.
     */
    public function store(Request $request)
    {
        $this->checkApiKey($request);

        $validated = $request->validate([
            'esp32_device_id' => ['required', 'string', 'max:100'],
            'voltage' => ['required', 'numeric'],
            'current' => ['required', 'numeric'],
            'power' => ['required', 'numeric'],
            'energy' => ['required', 'numeric'],
            'frequency' => ['nullable', 'numeric'],
            'power_factor' => ['nullable', 'numeric'],
        ]);

        $deviceQuery = Device::where('esp32_device_id', $validated['esp32_device_id']);

        if (Schema::hasColumn('devices', 'relay_code')) {
            $deviceQuery->orderBy('relay_code');
        } else {
            $deviceQuery->orderBy('id');
        }

        $device = $deviceQuery->first();

        if (! $device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan. Pastikan ESP32 Device ID sudah terdaftar di sistem.',
            ], 404);
        }

        $energyData = [
            'voltage' => $validated['voltage'],
            'current' => $validated['current'],
            'power' => $validated['power'],
            'energy' => $validated['energy'],
            'frequency' => $validated['frequency'] ?? null,
            'power_factor' => $validated['power_factor'] ?? null,
        ];

        /*
         * Ambil log terakhir untuk device ini.
         * Kalau sudah ada, update baris itu.
         * Kalau belum ada, buat satu baris baru.
         */
        $log = EnergyLog::where('device_id', $device->id)
            ->latest('id')
            ->first();

        $created = false;

        if ($log) {
            $log->update($energyData);
        } else {
            $log = EnergyLog::create(array_merge([
                'device_id' => $device->id,
            ], $energyData));

            $created = true;
        }

        /*
         * Bersihkan log lama untuk device yang sama.
         * Hasil akhirnya: 1 device hanya punya 1 baris data sensor.
         */
        EnergyLog::where('device_id', $device->id)
            ->where('id', '!=', $log->id)
            ->delete();

        $log->refresh();

        return response()->json([
            'success' => true,
            'message' => $created
                ? 'Data sensor berhasil dibuat.'
                : 'Data sensor berhasil diperbarui.',
            'data' => $log,
        ], $created ? 201 : 200);
    }

    /**
     * Mengirim status satu relay/device ke ESP32.
     *
     * Endpoint lama:
     * GET /api/device/{esp32_device_id}/command
     */
    public function command(Request $request, string $esp32_device_id)
    {
        $this->checkApiKey($request);

        $deviceQuery = Device::where('esp32_device_id', $esp32_device_id);

        if (Schema::hasColumn('devices', 'relay_code')) {
            $deviceQuery->orderBy('relay_code');
        } else {
            $deviceQuery->orderBy('id');
        }

        $device = $deviceQuery->first();

        if (! $device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan.',
            ], 404);
        }

        $systemSetting = $this->resolveSystemSetting($device);

        return response()->json([
            'success' => true,
            'esp32_device_id' => $device->esp32_device_id,
            'device_id' => $device->id,
            'device_name' => $device->name,
            'relay_code' => (string) ($device->relay_code ?? '1'),
            'relay' => (bool) $device->status,
            'status' => $device->status ? 'ON' : 'OFF',
            'refresh_interval' => $systemSetting?->refresh_interval ?? 5,
        ]);
    }

    /**
     * Mengirim semua status relay untuk satu ESP32.
     *
     * Endpoint baru:
     * GET /api/unit/{esp32_device_id}/commands
     *
     * Dipakai untuk sistem:
     * 1 ESP32 = banyak relay.
     */
    public function commands(Request $request, string $esp32_device_id)
    {
        $this->checkApiKey($request);

        $devicesQuery = Device::where('esp32_device_id', $esp32_device_id);

        if (Schema::hasColumn('devices', 'relay_code')) {
            $devicesQuery->orderBy('relay_code');
        } else {
            $devicesQuery->orderBy('id');
        }

        $devices = $devicesQuery->get();

        if ($devices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada device untuk ESP32 ini.',
                'esp32_device_id' => $esp32_device_id,
                'refresh_interval' => 5,
                'relays' => [],
            ], 404);
        }

        $firstDevice = $devices->first();
        $systemSetting = $this->resolveSystemSetting($firstDevice);

        $relays = $devices->map(function ($device) {
            $roomName = null;

            if (! empty($device->room_id)) {
                $roomName = Room::where('id', $device->room_id)->value('name');
            }

            return [
                'device_id' => $device->id,
                'device_name' => $device->name,
                'room_id' => $device->room_id,
                'room_name' => $roomName,
                'relay_code' => (string) ($device->relay_code ?? $device->id),
                'relay' => (bool) $device->status,
                'status' => $device->status ? 'ON' : 'OFF',
            ];
        })->values();

        return response()->json([
            'success' => true,
            'esp32_device_id' => $esp32_device_id,
            'refresh_interval' => $systemSetting?->refresh_interval ?? 5,
            'total_relays' => $relays->count(),
            'relays' => $relays,
        ]);
    }

    /**
     * Mengambil pengaturan sistem.
     *
     * Prioritas:
     * 1. Setting berdasarkan device_id jika kolom device_id tersedia.
     * 2. Setting berdasarkan user_id dari room.
     */
    private function resolveSystemSetting(Device $device): ?SystemSetting
    {
        if (Schema::hasColumn('system_settings', 'device_id')) {
            $systemSetting = SystemSetting::where('device_id', $device->id)->first();

            if ($systemSetting) {
                return $systemSetting;
            }
        }

        if (! empty($device->room_id)) {
            $roomUserId = Room::where('id', $device->room_id)->value('user_id');

            if ($roomUserId) {
                return SystemSetting::where('user_id', $roomUserId)->first();
            }
        }

        return null;
    }
}