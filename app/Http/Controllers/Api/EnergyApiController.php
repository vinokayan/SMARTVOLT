<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\EnergyLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class EnergyApiController extends Controller
{
    private function checkApiKey(Request $request): void
    {
        $apiKey = env('IOT_API_KEY', 'smartvolt-demo-key');

        if ($request->header('X-API-KEY') !== $apiKey) {
            abort(401, 'Invalid API Key');
        }
    }

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

        $device = Device::where('esp32_device_id', $validated['esp32_device_id'])->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan. Pastikan ESP32 Device ID sudah disimpan di System Settings.',
            ], 404);
        }

        $log = EnergyLog::create([
            'device_id' => $device->id,
            'voltage' => $validated['voltage'],
            'current' => $validated['current'],
            'power' => $validated['power'],
            'energy' => $validated['energy'],
            'frequency' => $validated['frequency'] ?? null,
            'power_factor' => $validated['power_factor'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data sensor berhasil disimpan.',
            'data' => $log,
        ]);
    }

    public function command(Request $request, string $esp32_device_id)
    {
        $this->checkApiKey($request);

        $device = Device::where('esp32_device_id', $esp32_device_id)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan.',
            ], 404);
        }

        $systemSetting = SystemSetting::where('device_id', $device->id)->first();

        return response()->json([
            'success' => true,
            'esp32_device_id' => $device->esp32_device_id,
            'device_name' => $device->name,
            'relay' => (bool) $device->status,
            'status' => $device->status ? 'ON' : 'OFF',
            'refresh_interval' => $systemSetting?->refresh_interval ?? 5,
        ]);
    }
}