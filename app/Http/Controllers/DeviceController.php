<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\EnergyLog;
use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use JsonException;
use PhpMqtt\Client\Contracts\MqttClient;
use PhpMqtt\Client\Facades\MQTT;
use Throwable;

class DeviceController extends Controller
{
    private const MQTT_PUBLISHER_CONNECTION = 'publisher';

    private const MQTT_COMMAND_QOS = 1;

    private const MQTT_COMMAND_RETAIN = false;

    private const MQTT_PUBLISH_WAIT_SECONDS = 10;

    private const DEFAULT_COMMAND_TIMEOUT_SECONDS = 30;

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
            ->whereHas('room', fn (Builder $query) => $query->where('user_id', Auth::id()))
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

            $room = Room::query()
                ->whereKey($request->input('room_id'))
                ->where('user_id', Auth::id())
                ->firstOrFail();
        }

        $this->ensureRoomOwner($room);
        $this->normalizeDeviceInput($request);

        $validated = $request->validate(
            $this->deviceValidationRules($room->id),
            $this->deviceValidationMessages()
        );

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
         * Kolom lama tetap diisi agar endpoint atau data lama yang masih memakai
         * esp32_device_id tidak langsung rusak.
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
        $this->normalizeDeviceInput($request);

        $validated = $request->validate(
            $this->deviceValidationRules($device->room_id, $device->id),
            $this->deviceValidationMessages()
        );

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

    public function toggle(Request $request, Device $device): JsonResponse
    {
        try {
            $this->ensureDeviceOwner($device);

            $relayCode = trim((string) $device->relay_code);
            $espUnitId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));

            if ($relayCode === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Perangkat belum memiliki saluran kontrol.',
                ], 422);
            }

            if (! in_array($relayCode, ['1', '2'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saluran kontrol perangkat tidak valid.',
                ], 422);
            }

            if ($espUnitId === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Perangkat belum terhubung ke unit SmartVolt.',
                ], 422);
            }

            if (! $this->isEspUnitOnline($espUnitId, $device->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'SmartVolt belum terhubung. Hubungkan kembali lalu coba lagi.',
                    'esp_online' => false,
                    'device_id' => $device->id,
                ], 409);
            }

            if (! $this->isDeviceStatusCurrent($device)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status perangkat terbaru belum tersedia. Tunggu beberapa saat lalu coba lagi.',
                    'esp_online' => true,
                    'device_id' => $device->id,
                ], 409);
            }

            /*
             * Kunci baris perangkat agar dua request dari pengguna atau dua tab
             * browser tidak membuat dua command pada waktu yang bersamaan.
             */
            $prepared = DB::transaction(function () use ($device, $relayCode, $espUnitId): array {
                /** @var Device $lockedDevice */
                $lockedDevice = Device::with('room')
                    ->whereKey($device->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->ensureDeviceOwner($lockedDevice);
                $this->clearExpiredPendingCommand($lockedDevice);

                if ($lockedDevice->pending_state !== null) {
                    return [
                        'pending' => true,
                        'device' => $lockedDevice,
                    ];
                }

                if (! $this->isEspUnitOnline($espUnitId, $lockedDevice->id)) {
                    return [
                        'offline' => true,
                        'device' => $lockedDevice,
                    ];
                }

                if (! $this->isDeviceStatusCurrent($lockedDevice)) {
                    return [
                        'stale' => true,
                        'device' => $lockedDevice,
                    ];
                }

                $requestedState = ! $this->isDeviceOn($lockedDevice->status);
                $commandId = (string) Str::uuid();
                $issuedAt = now();

                $lockedDevice->update([
                    'last_command_id' => $commandId,
                    'last_command_at' => $issuedAt,
                    'pending_state' => $requestedState,
                    'last_command_success' => null,
                ]);

                return [
                    'pending' => false,
                    'offline' => false,
                    'stale' => false,
                    'device' => $lockedDevice,
                    'command_id' => $commandId,
                    'requested_state' => $requestedState,
                    'issued_at' => $issuedAt,
                    'topic' => 'smartvolt/unit/' . $espUnitId . '/command',
                    'payload' => $this->buildCommandPayload(
                        $lockedDevice,
                        $espUnitId,
                        $relayCode,
                        $requestedState,
                        $commandId,
                        $issuedAt
                    ),
                ];
            }, 3);

            /** @var Device $preparedDevice */
            $preparedDevice = $prepared['device'];

            if (($prepared['pending'] ?? false) === true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perintah sebelumnya masih diproses.',
                    'pending' => true,
                    'device_id' => $preparedDevice->id,
                ], 409);
            }

            if (($prepared['offline'] ?? false) === true) {
                return response()->json([
                    'success' => false,
                    'message' => 'SmartVolt baru saja terputus. Hubungkan kembali lalu coba lagi.',
                    'esp_online' => false,
                    'device_id' => $preparedDevice->id,
                ], 409);
            }

            if (($prepared['stale'] ?? false) === true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status perangkat terbaru belum tersedia. Tunggu beberapa saat lalu coba lagi.',
                    'esp_online' => true,
                    'device_id' => $preparedDevice->id,
                ], 409);
            }

            $commandId = (string) $prepared['command_id'];
            $requestedState = (bool) $prepared['requested_state'];

            try {
                $this->publishRelayCommand(
                    (string) $prepared['topic'],
                    (string) $prepared['payload']
                );
            } catch (Throwable $exception) {
                $this->markCommandAsFailed(
                    $preparedDevice->id,
                    $commandId
                );

                throw $exception;
            }

            return response()->json([
                'success' => true,
                'confirmed' => false,
                'pending' => true,
                'message' => $requestedState
                    ? $preparedDevice->name . ' sedang dinyalakan.'
                    : $preparedDevice->name . ' sedang dimatikan.',
                'device_id' => $preparedDevice->id,
                'room_id' => $preparedDevice->room_id,
                'status' => $requestedState ? 'on' : 'off',
                'is_on' => $requestedState,
                'requested_state' => $requestedState,
                'status_available' => true,
                'esp_online' => true,
                'command_id' => $commandId,
            ], 202);
        } catch (Throwable $exception) {
            Log::error('Gagal mengirim perintah SmartVolt.', [
                'device_id' => $device->id ?? null,
                'user_id' => Auth::id(),
                'error' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Perintah tidak dapat dikirim. Periksa koneksi SmartVolt lalu coba lagi.',
            ], 500);
        }
    }

    private function buildCommandPayload(
        Device $device,
        string $espUnitId,
        string $relayCode,
        bool $requestedState,
        string $commandId,
        Carbon $issuedAt
    ): string {
        try {
            return json_encode([
                'relay_code' => $relayCode,
                'state' => $requestedState,
                'source' => 'laravel-dashboard',
                'device_id' => $device->id,
                'device_name' => $device->name,
                'esp32_device_id' => $espUnitId,
                'esp_unit_id' => $espUnitId,
                'command_id' => $commandId,
                'issued_at' => $issuedAt->toIso8601String(),
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new \RuntimeException(
                'Payload perintah tidak dapat dibuat.',
                previous: $exception
            );
        }
    }

    private function publishRelayCommand(string $topic, string $payload): void
    {
        /** @var MqttClient|null $mqtt */
        $mqtt = null;

        try {
            $mqtt = MQTT::connection(self::MQTT_PUBLISHER_CONNECTION);

            /*
             * QoS 1 memastikan broker mengirim PUBACK.
             * Retain harus false agar command ON/OFF tidak disimpan broker dan
             * tidak dijalankan kembali ketika ESP32 melakukan reconnect.
             */
            $mqtt->publish(
                $topic,
                $payload,
                self::MQTT_COMMAND_QOS,
                self::MQTT_COMMAND_RETAIN
            );

            /*
             * Proses PUBACK, lalu keluar saat antrean publish sudah kosong.
             * Batas waktu mencegah request web menggantung tanpa batas.
             */
            $mqtt->loop(
                true,
                true,
                self::MQTT_PUBLISH_WAIT_SECONDS
            );
        } finally {
            if ($mqtt !== null) {
                try {
                    MQTT::disconnect(self::MQTT_PUBLISHER_CONNECTION);
                } catch (Throwable) {
                    try {
                        $mqtt->disconnect();
                    } catch (Throwable) {
                        // Koneksi mungkin sudah ditutup broker.
                    }
                }
            }
        }
    }

    private function markCommandAsFailed(int $deviceId, string $commandId): void
    {
        Device::query()
            ->whereKey($deviceId)
            ->where('last_command_id', $commandId)
            ->update([
                'pending_state' => null,
                'last_command_success' => false,
            ]);
    }

    private function clearExpiredPendingCommand(Device $device): void
    {
        if ($device->pending_state === null || ! $this->pendingCommandExpired($device)) {
            return;
        }

        $device->update([
            'pending_state' => null,
            'last_command_success' => false,
        ]);

        $device->refresh();
    }

    private function pendingCommandExpired(Device $device): bool
    {
        if ($device->pending_state === null) {
            return false;
        }

        $lastCommandAt = $this->toCarbon($device->last_command_at);

        if ($lastCommandAt === null) {
            return true;
        }

        return $lastCommandAt->lte(
            now()->subSeconds($this->commandTimeoutSeconds())
        );
    }

    private function commandTimeoutSeconds(): int
    {
        return max(
            10,
            (int) config(
                'services.iot.command_timeout_seconds',
                self::DEFAULT_COMMAND_TIMEOUT_SECONDS
            )
        );
    }

    private function normalizeDeviceInput(Request $request): void
    {
        $deviceKey = $request->input('device_key');
        $relayCode = $request->input('relay_code') ?? $request->input('esp32_device_id');

        $request->merge([
            'name' => trim((string) $request->input('name')),
            'device_key' => $deviceKey === null || trim((string) $deviceKey) === ''
                ? null
                : trim((string) $deviceKey),
            'relay_code' => trim((string) $relayCode),
            'esp_unit_id' => trim((string) $request->input('esp_unit_id')),
        ]);
    }

    private function deviceValidationRules(int $roomId, ?int $ignoreDeviceId = null): array
    {
        $nameRule = Rule::unique('devices', 'name')
            ->where(fn ($query) => $query->where('room_id', $roomId));

        if ($ignoreDeviceId !== null) {
            $nameRule->ignore($ignoreDeviceId);
        }

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                $nameRule,
            ],
            'device_key' => ['nullable', 'string', 'max:100'],
            'relay_code' => ['required', 'string', Rule::in(['1', '2'])],
            'esp_unit_id' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9_-]+$/',
            ],
        ];
    }

    private function deviceValidationMessages(): array
    {
        return [
            'name.required' => 'Nama perangkat wajib diisi.',
            'name.unique' => 'Nama perangkat sudah ada di ruangan ini.',
            'relay_code.required' => 'Relay channel wajib diisi.',
            'relay_code.in' => 'Relay channel hanya boleh 1 atau 2.',
            'esp_unit_id.required' => 'ESP Unit ID wajib diisi.',
            'esp_unit_id.regex' => 'ESP Unit ID hanya boleh berisi huruf, angka, garis bawah, atau tanda hubung.',
        ];
    }

    private function validateRelayCodeIsUnique(
        string $relayCode,
        string $espUnitId,
        ?int $ignoreDeviceId = null
    ): void {
        if (! Schema::hasColumn('devices', 'relay_code')) {
            return;
        }

        /*
         * Kombinasi ESP Unit ID + relay harus unik secara global. Jika dibatasi
         * hanya per pengguna, dua akun dapat tanpa sengaja mengendalikan relay
         * fisik yang sama.
         */
        $query = Device::query()
            ->where('relay_code', $relayCode)
            ->where(function (Builder $query) use ($espUnitId): void {
                if (Schema::hasColumn('devices', 'esp_unit_id')) {
                    $query->where('esp_unit_id', $espUnitId);
                }

                if (Schema::hasColumn('devices', 'esp32_device_id')) {
                    if (Schema::hasColumn('devices', 'esp_unit_id')) {
                        $query->orWhere('esp32_device_id', $espUnitId);
                    } else {
                        $query->where('esp32_device_id', $espUnitId);
                    }
                }
            });

        if ($ignoreDeviceId !== null) {
            $query->whereKeyNot($ignoreDeviceId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'relay_code' => 'Relay channel sudah digunakan pada ESP Unit ID ini.',
            ]);
        }
    }

    private function isDeviceOn(mixed $status): bool
    {
        if (is_bool($status)) {
            return $status;
        }

        if (is_numeric($status)) {
            return (int) $status === 1;
        }

        return in_array(strtolower(trim((string) $status)), [
            'on',
            'nyala',
            'active',
            'aktif',
            'true',
            '1',
        ], true);
    }

    private function isEspUnitOnline(string $espUnitId, int $deviceId): bool
    {
        $espUnitId = trim($espUnitId);

        if ($espUnitId === '') {
            return false;
        }

        $threshold = now()->subMinutes($this->espOnlineTimeoutMinutes());

        if (
            Schema::hasColumn('devices', 'is_online')
            && Schema::hasColumn('devices', 'last_seen_at')
        ) {
            return Device::query()
                ->whereKey($deviceId)
                ->where(function (Builder $query) use ($espUnitId): void {
                    $query->where('esp_unit_id', $espUnitId)
                        ->orWhere('esp32_device_id', $espUnitId);
                })
                ->where('is_online', true)
                ->where('last_seen_at', '>=', $threshold)
                ->exists();
        }

        /* Fallback sementara sebelum migration status MQTT dijalankan. */
        if (
            ! Schema::hasTable('energy_meters')
            || ! Schema::hasTable('energy_logs')
            || ! Schema::hasColumn('energy_meters', 'esp_unit_id')
            || ! Schema::hasColumn('energy_logs', 'created_at')
        ) {
            return false;
        }

        return EnergyLog::query()
            ->where('created_at', '>=', $threshold)
            ->whereHas('energyMeter', function (Builder $query) use ($espUnitId): void {
                $query->where('esp_unit_id', $espUnitId)
                    ->where('is_active', true);
            })
            ->exists();
    }

    private function isDeviceStatusCurrent(Device $device): bool
    {
        if (
            ! Schema::hasColumn('devices', 'last_confirmed_at')
            || ! Schema::hasColumn('devices', 'last_seen_at')
            || ! Schema::hasColumn('devices', 'is_online')
        ) {
            return true;
        }

        $threshold = now()->subMinutes($this->espOnlineTimeoutMinutes());

        return (bool) $device->is_online
            && $this->timestampIsRecent($device->last_seen_at, $threshold)
            && $this->timestampIsRecent($device->last_confirmed_at, $threshold);
    }

    private function timestampIsRecent(mixed $value, Carbon $threshold): bool
    {
        $timestamp = $this->toCarbon($value);

        return $timestamp !== null && $timestamp->gte($threshold);
    }

    private function toCarbon(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        try {
            return Carbon::parse((string) $value);
        } catch (Throwable) {
            return null;
        }
    }

    private function espOnlineTimeoutMinutes(): int
    {
        return max(
            1,
            (int) config('services.iot.online_timeout_minutes', 2)
        );
    }

    private function redirectAfterAction(
        Request $request,
        string $message,
        ?int $roomId = null
    ) {
        if (in_array(
            $request->input('return_to'),
            ['technician', 'settings'],
            true
        )) {
            return redirect()
                ->route('technician.index')
                ->with('success', $message)
                ->with('status', $message)
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
