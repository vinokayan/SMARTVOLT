<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpMqtt\Client\Facades\MQTT;

class DeviceController extends Controller
{
    private function ensureRoomOwner(Room $room): void
    {
        if ((int) $room->user_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak punya akses ke ruangan ini.');
        }
    }

    private function ensureDeviceOwner(Device $device): void
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
            ->orderBy('name')
            ->get();

        return view('devices', compact('devices'));
    }

    public function store(Request $request, ?Room $room = null)
    {
        if (! $room) {
            $request->validate([
                'room_id' => [
                    'required',
                    Rule::exists('rooms', 'id')->where(function ($query) {
                        $query->where('user_id', Auth::id());
                    }),
                ],
            ], [
                'room_id.required' => 'Ruangan wajib dipilih.',
                'room_id.exists' => 'Ruangan tidak valid.',
            ]);

            $room = Room::where('id', $request->room_id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
        }

        $this->ensureRoomOwner($room);

        $relayInput = $request->input('relay_code') ?? $request->input('esp32_device_id');

        $request->merge([
            'relay_code' => $relayInput,
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices', 'name')->where(function ($query) use ($room) {
                    $query->where('room_id', $room->id);
                }),
            ],
            'device_key' => ['nullable', 'string', 'max:100'],
            'relay_code' => ['required', 'string', 'max:100'],
            'esp_unit_id' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama perangkat wajib diisi.',
            'name.unique' => 'Nama perangkat sudah ada di ruangan ini.',
            'relay_code.required' => 'Kode relay wajib diisi.',
            'esp_unit_id.required' => 'Kode sensor wajib diisi.',
        ]);

        $this->validateRelayCodeIsUnique($validated['relay_code']);
        // Kode sensor tidak dibuat unik agar satu PZEM/sensor bisa dipakai beberapa perangkat.

        $data = [
            'room_id' => $room->id,
            'name' => $validated['name'],
            'status' => false,
        ];

        if (Schema::hasColumn('devices', 'user_id')) {
            $data['user_id'] = Auth::id();
        }

        if (Schema::hasColumn('devices', 'type')) {
            $data['type'] = $validated['type'] ?? 'other';
        }

        if (Schema::hasColumn('devices', 'device_key')) {
            $data['device_key'] = $validated['device_key'] ?? null;
        }

        if (Schema::hasColumn('devices', 'relay_code')) {
            $data['relay_code'] = $validated['relay_code'];
        }

        if (Schema::hasColumn('devices', 'esp32_device_id')) {
            $data['esp32_device_id'] = $validated['relay_code'];
        }

        if (Schema::hasColumn('devices', 'esp_unit_id')) {
            $data['esp_unit_id'] = $validated['esp_unit_id'];
        }

        $device = Device::create($data);

        return $this->redirectAfterAction(
            $request,
            'Perangkat berhasil ditambahkan.',
            $device->room_id
        );
    }

    public function update(Request $request, Device $device)
    {
        $this->ensureDeviceOwner($device);

        $relayInput = $request->input('relay_code') ?? $request->input('esp32_device_id');

        $request->merge([
            'relay_code' => $relayInput,
        ]);

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
            'device_key' => ['nullable', 'string', 'max:100'],
            'relay_code' => ['required', 'string', 'max:100'],
            'esp_unit_id' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama perangkat wajib diisi.',
            'name.unique' => 'Nama perangkat sudah ada di ruangan ini.',
            'relay_code.required' => 'Kode relay wajib diisi.',
            'esp_unit_id.required' => 'Kode sensor wajib diisi.',
        ]);

        $this->validateRelayCodeIsUnique($validated['relay_code'], $device->id);
        // Kode sensor boleh sama untuk beberapa perangkat jika memakai satu sensor PZEM.

        $data = [
            'name' => $validated['name'],
        ];

        if (Schema::hasColumn('devices', 'type')) {
            $data['type'] = $validated['type'] ?? $device->type ?? 'other';
        }

        if (Schema::hasColumn('devices', 'device_key')) {
            $data['device_key'] = $validated['device_key'] ?? null;
        }

        if (Schema::hasColumn('devices', 'relay_code')) {
            $data['relay_code'] = $validated['relay_code'];
        }

        if (Schema::hasColumn('devices', 'esp32_device_id')) {
            $data['esp32_device_id'] = $validated['relay_code'];
        }

        if (Schema::hasColumn('devices', 'esp_unit_id')) {
            $data['esp_unit_id'] = $validated['esp_unit_id'];
        }

        $device->update($data);

        return $this->redirectAfterAction(
            $request,
            'Perangkat berhasil diperbarui.',
            $device->room_id
        );
    }

    public function destroy(Request $request, Device $device)
    {
        $this->ensureDeviceOwner($device);

        $roomId = $device->room_id;

        $device->delete();

        return $this->redirectAfterAction(
            $request,
            'Perangkat berhasil dihapus.',
            $roomId
        );
    }

    public function toggle(Request $request, Device $device)
    {
        try {
            $this->ensureDeviceOwner($device);

            $isCurrentlyOn = $this->isDeviceOn($device->status);
            $newStatus = ! $isCurrentlyOn;

            $relayCode = $device->relay_code ?? $device->esp32_device_id ?? null;

            if (! $relayCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perangkat belum memiliki kode relay.',
                ], 422);
            }

            $topic = 'smartvolt/user/' . Auth::id() . '/control/' . $relayCode;

            $payload = json_encode([
                'user_id' => Auth::id(),
                'device_id' => $device->id,
                'device_name' => $device->name,
                'relay_code' => $relayCode,
                'esp32_device_id' => $relayCode,
                'esp_unit_id' => $device->esp_unit_id ?? null,
                'relay' => $newStatus,
                'status' => $newStatus ? 'ON' : 'OFF',
            ]);

            MQTT::publish($topic, $payload, 0);

            $device->update([
                'status' => $newStatus,
            ]);

            return response()->json([
                'success' => true,
                'message' => $newStatus
                    ? $device->name . ' berhasil dinyalakan.'
                    : $device->name . ' berhasil dimatikan.',
                'device_id' => $device->id,
                'room_id' => $device->room_id,
                'status' => $newStatus ? 'on' : 'off',
                'label' => $newStatus ? 'Nyala' : 'Mati',
                'mqtt_topic' => $topic,
                'mqtt_payload' => json_decode($payload, true),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal toggle perangkat', [
                'device_id' => $device->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim perintah ke perangkat. Pastikan broker MQTT berjalan dan konfigurasi MQTT benar.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function validateRelayCodeIsUnique(string $relayCode, ?int $ignoreDeviceId = null): void
    {
        $columns = [];

        if (Schema::hasColumn('devices', 'relay_code')) {
            $columns[] = 'relay_code';
        }

        if (Schema::hasColumn('devices', 'esp32_device_id')) {
            $columns[] = 'esp32_device_id';
        }

        if (empty($columns)) {
            return;
        }

        $query = Device::whereHas('room', function ($query) {
            $query->where('user_id', Auth::id());
        })->where(function ($query) use ($columns, $relayCode) {
            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $query->where($column, $relayCode);
                } else {
                    $query->orWhere($column, $relayCode);
                }
            }
        });

        if ($ignoreDeviceId) {
            $query->where('id', '!=', $ignoreDeviceId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'relay_code' => 'Kode relay sudah digunakan di akun ini.',
            ]);
        }
    }

    private function isDeviceOn($status): bool
    {
        if (is_bool($status)) {
            return $status;
        }

        if (is_numeric($status)) {
            return (int) $status === 1;
        }

        $status = strtolower((string) $status);

        return in_array($status, [
            'on',
            'nyala',
            'active',
            'aktif',
            'true',
            '1',
        ], true);
    }

    private function redirectAfterAction(Request $request, string $message, ?int $roomId = null)
    {
        if ($request->input('return_to') === 'settings') {
            return redirect()
                ->route('settings')
                ->with('success', $message)
                ->with('status', $message)
                ->with('open_advanced_panel', true)
                ->with('selected_room_id', $roomId);
        }

        if ($request->input('return_to') === 'rooms') {
            return redirect()
                ->route('rooms')
                ->with('success', $message)
                ->with('status', $message)
                ->with('open_room_id', $roomId);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', $message)
            ->with('status', $message)
            ->with('open_room_id', $roomId);
    }
}
