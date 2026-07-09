<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpMqtt\Client\Facades\MQTT;

class DeviceController extends Controller
{
    private function ensureAdvancedMode(): void
    {
        if (! session('advanced_mode')) {
            abort(403, 'Mode Lanjutan belum aktif.');
        }
    }

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
            ->whereHas('room', fn ($query) => $query->where('user_id', Auth::id()))
            ->orderBy('name')
            ->get();

        return view('devices', compact('devices'));
    }

    public function store(Request $request, ?Room $room = null)
    {
        $this->ensureAdvancedMode();

        if (! $room) {
            $request->validate([
                'room_id' => [
                    'required',
                    Rule::exists('rooms', 'id')
                        ->where(fn ($query) => $query->where('user_id', Auth::id())),
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

        /*
         * Kompatibilitas:
         * Jika form lama masih mengirim esp32_device_id sebagai relay_code,
         * sistem tetap bisa membaca nilainya sebagai relay_code.
         */
        $request->merge([
            'relay_code' => $request->input('relay_code') ?? $request->input('esp32_device_id'),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices', 'name')
                    ->where(fn ($query) => $query->where('room_id', $room->id)),
            ],
            'device_key' => ['nullable', 'string', 'max:100'],
            'relay_code' => ['required', 'string', 'max:100'],
            'esp_unit_id' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama perangkat wajib diisi.',
            'name.unique' => 'Nama perangkat sudah ada di ruangan ini.',
            'relay_code.required' => 'Relay channel wajib diisi.',
            'esp_unit_id.required' => 'ESP Unit ID wajib diisi.',
        ]);

        $this->validateRelayCodeIsUnique(
            $validated['relay_code'],
            $validated['esp_unit_id']
        );

        $data = [
            'room_id' => $room->id,
            'name' => $validated['name'],
            'status' => false,
        ];

        if (Schema::hasColumn('devices', 'user_id')) {
            $data['user_id'] = Auth::id();
        }

        if (Schema::hasColumn('devices', 'device_key')) {
            $data['device_key'] = $validated['device_key'] ?? null;
        }

        if (Schema::hasColumn('devices', 'relay_code')) {
            $data['relay_code'] = $validated['relay_code'];
        }

        if (Schema::hasColumn('devices', 'esp_unit_id')) {
            $data['esp_unit_id'] = $validated['esp_unit_id'];
        }

        /*
         * Legacy:
         * Kolom esp32_device_id tetap diisi ESP Unit ID agar API lama
         * yang masih memakai esp32_device_id tidak langsung rusak.
         */
        if (Schema::hasColumn('devices', 'esp32_device_id')) {
            $data['esp32_device_id'] = $validated['esp_unit_id'];
        }

        $device = Device::create($data);

        return $this->redirectAfterAction(
            $request,
            'Relay berhasil ditambahkan.',
            $device->room_id
        );
    }

    public function update(Request $request, Device $device)
    {
        $this->ensureAdvancedMode();
        $this->ensureDeviceOwner($device);

        $request->merge([
            'relay_code' => $request->input('relay_code') ?? $request->input('esp32_device_id'),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices', 'name')
                    ->where(fn ($query) => $query->where('room_id', $device->room_id))
                    ->ignore($device->id),
            ],
            'device_key' => ['nullable', 'string', 'max:100'],
            'relay_code' => ['required', 'string', 'max:100'],
            'esp_unit_id' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama perangkat wajib diisi.',
            'name.unique' => 'Nama perangkat sudah ada di ruangan ini.',
            'relay_code.required' => 'Relay channel wajib diisi.',
            'esp_unit_id.required' => 'ESP Unit ID wajib diisi.',
        ]);

        $this->validateRelayCodeIsUnique(
            $validated['relay_code'],
            $validated['esp_unit_id'],
            $device->id
        );

        $data = [
            'name' => $validated['name'],
        ];

        if (Schema::hasColumn('devices', 'device_key')) {
            $data['device_key'] = $validated['device_key'] ?? null;
        }

        if (Schema::hasColumn('devices', 'relay_code')) {
            $data['relay_code'] = $validated['relay_code'];
        }

        if (Schema::hasColumn('devices', 'esp_unit_id')) {
            $data['esp_unit_id'] = $validated['esp_unit_id'];
        }

        if (Schema::hasColumn('devices', 'esp32_device_id')) {
            $data['esp32_device_id'] = $validated['esp_unit_id'];
        }

        $device->update($data);

        return $this->redirectAfterAction(
            $request,
            'Relay berhasil diperbarui.',
            $device->room_id
        );
    }

    public function destroy(Request $request, Device $device)
    {
        $this->ensureAdvancedMode();
        $this->ensureDeviceOwner($device);

        $roomId = $device->room_id;
        $device->delete();

        return $this->redirectAfterAction(
            $request,
            'Relay berhasil dihapus.',
            $roomId
        );
    }

    public function toggle(Request $request, Device $device)
    {
        try {
            $this->ensureDeviceOwner($device);

            $isCurrentlyOn = $this->isDeviceOn($device->status);
            $newStatus = ! $isCurrentlyOn;

            $relayCode = (string) $device->relay_code;

            if (! $relayCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relay belum memiliki relay channel.',
                ], 422);
            }

            $espUid = (string) ($device->esp_unit_id ?: $device->esp32_device_id);

            if (! $espUid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relay belum memiliki ESP Unit ID.',
                ], 422);
            }

            /*
             * Topic ini masih mengikuti firmware Anda saat ini:
             * smartvolt/unit/{ESP_UNIT_ID}/command
             */
            $topic = 'smartvolt/unit/' . $espUid . '/command';

            $payloadArray = [
                'relay_code' => $relayCode,
                'state' => (bool) $newStatus,
                'source' => 'laravel-dashboard',
                'device_id' => $device->id,
                'device_name' => $device->name,
                'esp32_device_id' => $espUid,
                'esp_unit_id' => $espUid,
                'command_id' => (string) Str::uuid(),
            ];

            $payload = json_encode($payloadArray, JSON_UNESCAPED_SLASHES);

            MQTT::publish($topic, $payload, 0);

            /*
             * Catatan:
             * Ini masih update langsung setelah publish MQTT.
             * Nanti untuk versi lebih profesional, bagian ini diganti
             * dengan acknowledgement dari ESP32.
             */
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
                'mqtt_payload' => $payloadArray,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal toggle relay', [
                'device_id' => $device->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim perintah ke relay. Pastikan broker MQTT berjalan dan konfigurasi MQTT benar.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function validateRelayCodeIsUnique(
        string $relayCode,
        string $espUnitId,
        ?int $ignoreDeviceId = null
    ): void {
        if (! Schema::hasColumn('devices', 'relay_code')) {
            return;
        }

        $query = Device::whereHas(
            'room',
            fn ($query) => $query->where('user_id', Auth::id())
        )
            ->where('relay_code', $relayCode);

        if (Schema::hasColumn('devices', 'esp_unit_id')) {
            $query->where('esp_unit_id', $espUnitId);
        }

        if ($ignoreDeviceId) {
            $query->where('id', '!=', $ignoreDeviceId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'relay_code' => 'Relay channel sudah digunakan pada ESP Unit ID ini.',
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

        return in_array(strtolower((string) $status), [
            'on',
            'nyala',
            'active',
            'aktif',
            'true',
            '1',
        ], true);
    }

    private function redirectAfterAction(
        Request $request,
        string $message,
        ?int $roomId = null
    ) {
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