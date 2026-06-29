<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use PhpMqtt\Client\Facades\MQTT;

class DeviceController extends Controller
{
    private function ensureAdvancedMode()
    {
        if (! session('advanced_mode')) {
            abort(403, 'Mode Lanjutan belum aktif.');
        }
    }

    private function ensureDeviceOwner(Device $device)
    {
        $device->loadMissing('room');

        if (! $device->room || (int) $device->room->user_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak punya akses ke perangkat ini.');
        }
    }

    public function index()
    {
        $devices = Device::with('room')
            ->whereHas('room', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->latest()
            ->get();

        return view('devices', compact('devices'));
    }

    public function store(Request $request)
    {
        $this->ensureAdvancedMode();

        $userRoomIds = Room::where('user_id', Auth::id())
            ->pluck('id')
            ->toArray();

        $validated = $request->validate([
            'room_id' => [
                'required',
                Rule::exists('rooms', 'id')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices', 'name')->where(function ($query) use ($request) {
                    $query->where('room_id', $request->room_id);
                }),
            ],

            'type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'esp32_device_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices', 'esp32_device_id')->where(function ($query) use ($userRoomIds) {
                    $query->whereIn('room_id', $userRoomIds);
                }),
            ],

            'esp_unit_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('devices', 'esp_unit_id'),
            ],
        ], [
            'room_id.required' => 'Ruangan wajib dipilih.',
            'room_id.exists' => 'Ruangan tidak valid.',

            'name.required' => 'Nama perangkat wajib diisi.',
            'name.max' => 'Nama perangkat maksimal 100 karakter.',
            'name.unique' => 'Nama perangkat sudah ada di ruangan ini.',

            'esp32_device_id.required' => 'Kode device / relay wajib diisi.',
            'esp32_device_id.unique' => 'Kode device / relay sudah digunakan di akun ini.',

            'esp_unit_id.unique' => 'Kode pengukur listrik sudah digunakan oleh perangkat lain.',
        ]);

        $room = Room::where('id', $validated['room_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $device = Device::create([
            'room_id' => $room->id,
            'name' => $validated['name'],
            'type' => $validated['type'] ?? 'other',
            'esp32_device_id' => $validated['esp32_device_id'],
            'esp_unit_id' => $validated['esp_unit_id'] ?? null,
            'status' => false,
        ]);

        if ($request->input('return_to') === 'settings') {
            return redirect()
                ->route('settings')
                ->with('status', 'Perangkat berhasil ditambahkan.')
                ->with('open_advanced_panel', true)
                ->with('selected_room_id', $device->room_id);
        }

        return back()
            ->with('status', 'Perangkat berhasil ditambahkan.');
    }

    public function update(Request $request, Device $device)
    {
        $this->ensureAdvancedMode();
        $this->ensureDeviceOwner($device);

        $userRoomIds = Room::where('user_id', Auth::id())
            ->pluck('id')
            ->toArray();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices', 'name')
                    ->where(function ($query) use ($device) {
                        $query->where('room_id', $device->room_id);
                    })
                    ->ignore($device->id),
            ],

            'type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'esp32_device_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices', 'esp32_device_id')
                    ->where(function ($query) use ($userRoomIds) {
                        $query->whereIn('room_id', $userRoomIds);
                    })
                    ->ignore($device->id),
            ],

            'esp_unit_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('devices', 'esp_unit_id')->ignore($device->id),
            ],
        ], [
            'name.required' => 'Nama perangkat wajib diisi.',
            'name.max' => 'Nama perangkat maksimal 100 karakter.',
            'name.unique' => 'Nama perangkat sudah ada di ruangan ini.',

            'esp32_device_id.required' => 'Kode device / relay wajib diisi.',
            'esp32_device_id.unique' => 'Kode device / relay sudah digunakan di akun ini.',

            'esp_unit_id.unique' => 'Kode pengukur listrik sudah digunakan oleh perangkat lain.',
        ]);

        $device->update([
            'name' => $validated['name'],
            'type' => $validated['type'] ?? $device->type,
            'esp32_device_id' => $validated['esp32_device_id'],
            'esp_unit_id' => $validated['esp_unit_id'] ?? null,
        ]);

        if ($request->input('return_to') === 'settings') {
            return redirect()
                ->route('settings')
                ->with('status', 'Perangkat berhasil diperbarui.')
                ->with('open_advanced_panel', true)
                ->with('selected_room_id', $device->room_id);
        }

        return back()
            ->with('status', 'Perangkat berhasil diperbarui.');
    }

    public function destroy(Request $request, Device $device)
    {
        $this->ensureAdvancedMode();
        $this->ensureDeviceOwner($device);

        $roomId = $device->room_id;
        $device->delete();

        if ($request->input('return_to') === 'settings') {
            return redirect()
                ->route('settings')
                ->with('status', 'Perangkat berhasil dihapus.')
                ->with('open_advanced_panel', true)
                ->with('selected_room_id', $roomId);
        }

        return back()
            ->with('status', 'Perangkat berhasil dihapus.');
    }

    public function toggle(Request $request, Device $device)
    {
        $device->load('room');

        if (! $device->room || (int) $device->room->user_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak punya akses ke perangkat ini.');
        }

        $newStatus = ! (bool) $device->status;

        if (! $device->esp32_device_id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perangkat belum memiliki kode device / relay.',
                ], 422);
            }

            return back()->withErrors([
                'device' => 'Perangkat belum memiliki kode device / relay.',
            ]);
        }

        $topic = 'smartvolt/user/' . Auth::id() . '/control/' . $device->esp32_device_id;

        $payload = json_encode([
            'user_id' => Auth::id(),
            'esp32_device_id' => $device->esp32_device_id,
            'esp_unit_id' => $device->esp_unit_id,
            'device_id' => $device->id,
            'device_name' => $device->name,
            'relay' => $newStatus,
            'status' => $newStatus ? 'ON' : 'OFF',
        ]);

        try {
            MQTT::publish($topic, $payload, 0);

            $device->update([
                'status' => $newStatus,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'status' => $device->status ? 'on' : 'off',
                    'label' => $device->status ? 'Nyala' : 'Mati',
                    'mqtt_topic' => $topic,
                    'mqtt_payload' => json_decode($payload, true),
                ]);
            }

            return back()
                ->with(
                    'status',
                    $newStatus
                        ? $device->name . ' berhasil dinyalakan.'
                        : $device->name . ' berhasil dimatikan.'
                )
                ->with('open_room_id', $request->open_room_id);
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim perintah MQTT', [
                'topic' => $topic,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim perintah ke perangkat. Pastikan broker MQTT berjalan.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return back()->withErrors([
                'mqtt' => 'Gagal mengirim perintah ke perangkat. Pastikan broker MQTT berjalan.',
            ]);
        }
    }
}