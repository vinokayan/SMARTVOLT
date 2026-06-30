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
        $this->validateSensorCodeIsUnique($validated['esp_unit_id']);

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
        $this->validateSensorCodeIsUnique($validated['esp_unit_id'], $device->id);

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
        $this->ensureDeviceOwner($device);

        $isCurrentlyOn = (bool) $device->status;
        $newStatus = ! $isCurrentlyOn;

        $relayCode = $device->relay_code ?? $device->esp32_device_id ?? null;

        if (! $relayCode) {
            return back()->withErrors([
                'device' => 'Perangkat belum memiliki kode relay.',
            ]);
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

        try {
            MQTT::publish($topic, $payload, 0);

            $device->update([
                'status' => $newStatus,
            ]);

            return back()
                ->with('success', $newStatus
                    ? $device->name . ' berhasil dinyalakan.'
                    : $device->name . ' berhasil dimatikan.'
                )
                ->with('status', $newStatus
                    ? $device->name . ' berhasil dinyalakan.'
                    : $device->name . ' berhasil dimatikan.'
                )
                ->with('selected_room_id', $device->room_id)
                ->with('open_advanced_panel', true);
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim perintah MQTT', [
                'topic' => $topic,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'mqtt' => 'Gagal mengirim perintah ke perangkat. Pastikan broker MQTT berjalan.',
            ]);
        }
    }

    private function validateRelayCodeIsUnique(string $relayCode, ?int $ignoreDeviceId = null): void
    {
        $column = null;

        if (Schema::hasColumn('devices', 'relay_code')) {
            $column = 'relay_code';
        } elseif (Schema::hasColumn('devices', 'esp32_device_id')) {
            $column = 'esp32_device_id';
        }

        if (! $column) {
            return;
        }

        $query = Device::where($column, $relayCode)
            ->whereHas('room', function ($query) {
                $query->where('user_id', Auth::id());
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

    private function validateSensorCodeIsUnique(string $sensorCode, ?int $ignoreDeviceId = null): void
    {
        if (! Schema::hasColumn('devices', 'esp_unit_id')) {
            return;
        }

        $query = Device::where('esp_unit_id', $sensorCode);

        if ($ignoreDeviceId) {
            $query->where('id', '!=', $ignoreDeviceId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'esp_unit_id' => 'Kode sensor sudah digunakan oleh perangkat lain.',
            ]);
        }
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

        return redirect()
            ->route('dashboard')
            ->with('success', $message)
            ->with('status', $message)
            ->with('open_room_id', $roomId);
    }
}