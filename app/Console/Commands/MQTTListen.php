<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use JsonException;
use PhpMqtt\Client\Facades\MQTT;
use Throwable;

class MQTTListen extends Command
{
    protected $signature = 'mqtt:listen';

    protected $description = 'Mendengarkan ACK dan status perangkat SmartVolt';

    private const LISTENER_CONNECTION = 'listener';

    private const ACK_TOPIC = 'smartvolt/unit/+/ack';

    private const STATUS_TOPIC = 'smartvolt/unit/+/status';

    private const RECONNECT_DELAY_SECONDS = 3;

    private const MAX_PAYLOAD_BYTES = 16384;

    private const PENDING_COMMAND_TIMEOUT_SECONDS = 30;

    /*
     * Digunakan untuk mencegah dua proses mqtt:listen berjalan
     * bersamaan pada komputer/server yang sama.
     *
     * @var resource|null
     */
    private $processLockHandle = null;

    public function handle(): int
    {
        if (! $this->acquireProcessLock()) {
            $this->error(
                'Listener SmartVolt lain masih berjalan. '
                . 'Hentikan proses lama sebelum menjalankan listener baru.'
            );

            return self::FAILURE;
        }

        $this->info('Listener SmartVolt dimulai.');
        $this->line('Koneksi: ' . self::LISTENER_CONNECTION);
        $this->line('ACK    : ' . self::ACK_TOPIC);
        $this->line('Status : ' . self::STATUS_TOPIC);

        try {
            while (true) {
                $mqtt = null;

                try {
                    /*
                     * Gunakan koneksi listener khusus.
                     *
                     * Jangan menggunakan MQTT::connection() tanpa nama,
                     * karena koneksi default sekarang digunakan publisher.
                     */
                    $mqtt = MQTT::connection(
                        self::LISTENER_CONNECTION
                    );

                    $mqtt->subscribe(
                        self::ACK_TOPIC,
                        function (
                            string $topic,
                            string $message,
                            bool $retained = false,
                            array $matchedWildcards = []
                        ): void {
                            $this->processAckMessage(
                                $topic,
                                $message,
                                $retained
                            );
                        },
                        1
                    );

                    $mqtt->subscribe(
                        self::STATUS_TOPIC,
                        function (
                            string $topic,
                            string $message,
                            bool $retained = false,
                            array $matchedWildcards = []
                        ): void {
                            $this->processStatusMessage(
                                $topic,
                                $message,
                                $retained
                            );
                        },
                        1
                    );

                    $this->info(
                        'Terhubung ke MQTT broker dan menunggu pesan...'
                    );

                    /*
                     * Event loop diperlukan agar subscription, keep-alive,
                     * status, dan ACK terus diproses.
                     */
                    $mqtt->loop(true);

                    /*
                     * Jika event loop dihentikan secara normal,
                     * jangan langsung membuat koneksi baru.
                     */
                    return self::SUCCESS;
                } catch (Throwable $exception) {
                    $this->error(
                        'Koneksi MQTT terputus: '
                        . $exception->getMessage()
                    );

                    Log::error(
                        'SmartVolt MQTT listener terputus.',
                        [
                            'connection' => self::LISTENER_CONNECTION,
                            'error' => $exception->getMessage(),
                            'exception' => $exception::class,
                        ]
                    );
                } finally {
                    $this->disconnectListener($mqtt);
                }

                $this->warn(
                    'Mencoba terhubung kembali dalam '
                    . self::RECONNECT_DELAY_SECONDS
                    . ' detik...'
                );

                sleep(self::RECONNECT_DELAY_SECONDS);
            }
        } finally {
            $this->releaseProcessLock();
        }
    }

    private function processAckMessage(
        string $topic,
        string $message,
        bool $retained
    ): void {
        try {
            /*
             * ACK tidak boleh retained.
             *
             * Jika ACK retained diterima, kemungkinan broker masih
             * menyimpan ACK lama. Pesan tersebut tidak boleh mengubah
             * status perangkat.
             */
            if ($retained) {
                $this->warn(
                    'ACK retained diabaikan agar status lama '
                    . 'tidak menimpa kondisi terbaru.'
                );

                Log::warning(
                    'ACK retained SmartVolt diabaikan.',
                    [
                        'topic' => $topic,
                    ]
                );

                return;
            }

            $payload = $this->decodePayload($message);

            if ($payload === null) {
                return;
            }

            $espUnitId = $this->resolveEspUnitId(
                $topic,
                $payload
            );

            if ($espUnitId === null) {
                return;
            }

            $relayCode = trim(
                (string) ($payload['relay_code'] ?? '')
            );

            $commandId = trim(
                (string) ($payload['command_id'] ?? '')
            );

            $actualState = $this->booleanValue(
                $payload['actual_state'] ?? null
            );

            $success = $this->booleanValue(
                $payload['success'] ?? null
            );

            if ($relayCode === '') {
                $this->warn(
                    'ACK diabaikan karena relay_code tidak tersedia.'
                );

                return;
            }

            if ($actualState === null) {
                $this->warn(
                    'ACK diabaikan karena actual_state tidak valid.'
                );

                return;
            }

            /*
             * ACK membuktikan ESP32 masih terhubung.
             * Semua perangkat dalam ESP Unit ID yang sama diperbarui.
             */
            $this->markEspUnitOnline($espUnitId);

            /*
             * command_id wajib agar ACK hanya diterapkan pada
             * perintah yang benar.
             */
            if ($commandId === '') {
                $this->warn(
                    'ACK diabaikan karena command_id tidak tersedia.'
                );

                Log::warning(
                    'ACK SmartVolt tanpa command_id diabaikan.',
                    [
                        'esp_unit_id' => $espUnitId,
                        'relay_code' => $relayCode,
                    ]
                );

                return;
            }

            /*
             * Cari perangkat berdasarkan:
             *
             * - ESP Unit ID;
             * - relay_code;
             * - command_id terakhir.
             *
             * Ini mencegah ACK dari perintah lama atau akun lain
             * diterapkan ke perangkat yang salah.
             */
            $device = $this->deviceQuery($espUnitId)
                ->where('relay_code', $relayCode)
                ->where('last_command_id', $commandId)
                ->first();

            if (! $device) {
                $relayExists = $this->deviceQuery($espUnitId)
                    ->where('relay_code', $relayCode)
                    ->exists();

                if ($relayExists) {
                    $this->warn(
                        "ACK lama atau tidak dikenal diabaikan: "
                        . "ESP {$espUnitId}, relay {$relayCode}."
                    );

                    Log::warning(
                        'ACK lama atau tidak dikenal diabaikan.',
                        [
                            'esp_unit_id' => $espUnitId,
                            'relay_code' => $relayCode,
                            'command_id' => $commandId,
                        ]
                    );
                } else {
                    $this->warn(
                        "Perangkat tidak ditemukan: "
                        . "ESP {$espUnitId}, relay {$relayCode}."
                    );
                }

                return;
            }

            /*
             * Jika firmware tidak mengirim field success,
             * bandingkan actual_state dengan pending_state.
             */
            if ($success === null) {
                $success = $device->pending_state !== null
                    && (bool) $device->pending_state === $actualState;
            }

            $confirmedAt = now();

            $device->update([
                'status' => $actualState,
                'is_online' => true,
                'last_seen_at' => $confirmedAt,
                'last_confirmed_at' => $confirmedAt,
                'last_ack_command_id' => $commandId,
                'pending_state' => null,
                'last_command_success' => $success,
            ]);

            $stateLabel = $actualState
                ? 'AKTIF'
                : 'TIDAK AKTIF';

            $resultLabel = $success
                ? 'berhasil'
                : 'tidak sesuai perintah';

            $this->info(
                "ACK diterima: {$device->name} "
                . "= {$stateLabel} ({$resultLabel})."
            );
        } catch (Throwable $exception) {
            Log::error(
                'Gagal memproses ACK SmartVolt.',
                [
                    'topic' => $topic,
                    'message' => $this->messageForLog($message),
                    'error' => $exception->getMessage(),
                    'exception' => $exception::class,
                ]
            );

            $this->error(
                'Gagal memproses ACK: '
                . $exception->getMessage()
            );
        }
    }

    private function processStatusMessage(
        string $topic,
        string $message,
        bool $retained
    ): void {
        try {
            $payload = $this->decodePayload($message);

            if ($payload === null) {
                return;
            }

            $espUnitId = $this->resolveEspUnitId(
                $topic,
                $payload
            );

            if ($espUnitId === null) {
                return;
            }

            /*
             * Field online wajib tersedia dan harus dapat dibaca
             * sebagai boolean.
             *
             * Jangan menganggap field yang hilang sebagai false,
             * karena payload rusak dapat membuat website terlihat offline.
             */
            $online = $this->booleanValue(
                $payload['online'] ?? null
            );

            if ($online === null) {
                $this->warn(
                    "Status SmartVolt {$espUnitId} diabaikan "
                    . 'karena field online tidak valid.'
                );

                return;
            }

            $devices = $this->deviceQuery($espUnitId)->get();

            if ($devices->isEmpty()) {
                $this->warn(
                    "Belum ada perangkat untuk ESP Unit ID "
                    . "{$espUnitId}."
                );

                return;
            }

            /*
             * Retained online status merupakan data yang disimpan broker,
             * bukan heartbeat baru.
             *
             * Tunggu status langsung berikutnya agar last_seen_at tidak
             * diperbarui menggunakan data lama.
             *
             * Retained offline tetap diproses karena dapat berasal dari
             * Last Will ESP32.
             */
            if ($retained && $online) {
                $this->line(
                    "Status retained SmartVolt {$espUnitId} diterima. "
                    . 'Menunggu heartbeat terbaru...'
                );

                return;
            }

            if (! $online) {
                foreach ($devices as $device) {
                    $attributes = [
                        'is_online' => false,
                    ];

                    /*
                     * Perintah yang sedang menunggu tidak boleh menggantung
                     * setelah ESP32 dinyatakan offline.
                     */
                    if ($device->pending_state !== null) {
                        $attributes['pending_state'] = null;
                        $attributes['last_command_success'] = false;
                    }

                    $device->update($attributes);
                }

                $this->warn(
                    "SmartVolt {$espUnitId} tidak terhubung."
                );

                return;
            }

            $receivedAt = now();

            foreach ($devices as $device) {
                $relayCode = trim(
                    (string) $device->relay_code
                );

                $relayKey = 'relay_' . $relayCode;

                $relayState = array_key_exists(
                    $relayKey,
                    $payload
                )
                    ? $this->booleanValue($payload[$relayKey])
                    : null;

                $attributes = [
                    'is_online' => true,
                    'last_seen_at' => $receivedAt,
                ];

                if ($relayState !== null) {
                    /*
                     * Status fisik relay dari ESP32 menjadi sumber utama
                     * kondisi ON/OFF yang ditampilkan website.
                     */
                    $attributes['status'] = $relayState;
                    $attributes['last_confirmed_at'] = $receivedAt;
                }

                if ($device->pending_state !== null) {
                    $requestedState = (bool) $device->pending_state;

                    /*
                     * Heartbeat menjadi konfirmasi cadangan ketika ACK
                     * tidak sampai, tetapi kondisi relay sudah sesuai.
                     */
                    if (
                        $relayState !== null
                        && $requestedState === $relayState
                    ) {
                        $attributes['pending_state'] = null;
                        $attributes['last_command_success'] = true;

                        Log::info(
                            'Perintah SmartVolt dikonfirmasi melalui status.',
                            [
                                'device_id' => $device->id,
                                'esp_unit_id' => $espUnitId,
                                'relay_code' => $relayCode,
                                'command_id' => $device->last_command_id,
                                'actual_state' => $relayState,
                            ]
                        );
                    } elseif ($this->pendingCommandExpired($device)) {
                        /*
                         * Jangan membiarkan pending_state tersimpan
                         * selamanya jika ACK dan perubahan relay gagal.
                         */
                        $attributes['pending_state'] = null;
                        $attributes['last_command_success'] = false;

                        Log::warning(
                            'Perintah SmartVolt melewati batas waktu.',
                            [
                                'device_id' => $device->id,
                                'esp_unit_id' => $espUnitId,
                                'relay_code' => $relayCode,
                                'command_id' => $device->last_command_id,
                            ]
                        );
                    }
                }

                $device->update($attributes);
            }

            $this->line(
                "Status SmartVolt {$espUnitId} diperbarui."
            );
        } catch (Throwable $exception) {
            Log::error(
                'Gagal memproses status SmartVolt.',
                [
                    'topic' => $topic,
                    'message' => $this->messageForLog($message),
                    'error' => $exception->getMessage(),
                    'exception' => $exception::class,
                ]
            );

            $this->error(
                'Gagal memproses status: '
                . $exception->getMessage()
            );
        }
    }

    private function decodePayload(string $message): ?array
    {
        if ($message === '') {
            $this->warn(
                'Pesan MQTT kosong dan telah diabaikan.'
            );

            return null;
        }

        if (strlen($message) > self::MAX_PAYLOAD_BYTES) {
            $this->warn(
                'Pesan MQTT terlalu besar dan telah diabaikan.'
            );

            Log::warning(
                'Payload MQTT SmartVolt terlalu besar.',
                [
                    'payload_bytes' => strlen($message),
                    'maximum_bytes' => self::MAX_PAYLOAD_BYTES,
                ]
            );

            return null;
        }

        try {
            $payload = json_decode(
                $message,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            $this->warn(
                'Pesan MQTT diabaikan karena JSON tidak valid.'
            );

            Log::warning(
                'JSON MQTT SmartVolt tidak valid.',
                [
                    'error' => $exception->getMessage(),
                    'message' => $this->messageForLog($message),
                ]
            );

            return null;
        }

        if (! is_array($payload)) {
            $this->warn(
                'Pesan MQTT diabaikan karena payload bukan objek JSON.'
            );

            return null;
        }

        return $payload;
    }

    private function resolveEspUnitId(
        string $topic,
        array $payload
    ): ?string {
        $topicEspUnitId = trim(
            (string) ($this->espUnitIdFromTopic($topic) ?? '')
        );

        $payloadEspUnitId = trim(
            (string) ($payload['esp_unit_id'] ?? '')
        );

        /*
         * Jika topic dan payload sama-sama memiliki ID,
         * keduanya wajib cocok.
         */
        if (
            $topicEspUnitId !== ''
            && $payloadEspUnitId !== ''
            && $topicEspUnitId !== $payloadEspUnitId
        ) {
            $this->warn(
                'Pesan MQTT ditolak karena ESP Unit ID '
                . 'pada topic dan payload berbeda.'
            );

            Log::warning(
                'ESP Unit ID MQTT tidak cocok.',
                [
                    'topic' => $topic,
                    'topic_esp_unit_id' => $topicEspUnitId,
                    'payload_esp_unit_id' => $payloadEspUnitId,
                ]
            );

            return null;
        }

        $espUnitId = $payloadEspUnitId !== ''
            ? $payloadEspUnitId
            : $topicEspUnitId;

        if ($espUnitId === '') {
            $this->warn(
                'Pesan MQTT diabaikan karena ESP Unit ID '
                . 'tidak tersedia.'
            );

            return null;
        }

        return $espUnitId;
    }

    private function espUnitIdFromTopic(
        string $topic
    ): ?string {
        $parts = explode(
            '/',
            trim($topic, '/')
        );

        /*
         * Format:
         *
         * smartvolt/unit/{ESP_UNIT_ID}/status
         * smartvolt/unit/{ESP_UNIT_ID}/ack
         */
        if (
            count($parts) < 4
            || $parts[0] !== 'smartvolt'
            || $parts[1] !== 'unit'
        ) {
            return null;
        }

        $espUnitId = trim((string) $parts[2]);

        return $espUnitId !== ''
            ? $espUnitId
            : null;
    }

    private function deviceQuery(
        string $espUnitId
    ): Builder {
        $espUnitId = trim($espUnitId);

        return Device::query()
            ->where(
                function (Builder $query) use ($espUnitId): void {
                    $query
                        ->where('esp_unit_id', $espUnitId)
                        ->orWhere(
                            'esp32_device_id',
                            $espUnitId
                        );
                }
            );
    }

    private function markEspUnitOnline(
        string $espUnitId
    ): void {
        $this->deviceQuery($espUnitId)->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);
    }

    private function pendingCommandExpired(
        Device $device
    ): bool {
        if ($device->pending_state === null) {
            return false;
        }

        if ($device->last_command_at === null) {
            return true;
        }

        $lastCommandAt = $device->last_command_at;

        if ($lastCommandAt instanceof \DateTimeInterface) {
            $lastCommandTimestamp = $lastCommandAt->getTimestamp();
        } else {
            $lastCommandTimestamp = strtotime(
                (string) $lastCommandAt
            );

            if ($lastCommandTimestamp === false) {
                return true;
            }
        }

        $timeoutSeconds = max(
            10,
            (int) config(
                'services.iot.command_timeout_seconds',
                self::PENDING_COMMAND_TIMEOUT_SECONDS
            )
        );

        return $lastCommandTimestamp
            <= now()->subSeconds($timeoutSeconds)->getTimestamp();
    }

    private function booleanValue(
        mixed $value
    ): ?bool {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return match ($value) {
                1 => true,
                0 => false,
                default => null,
            };
        }

        if (is_float($value)) {
            return match ($value) {
                1.0 => true,
                0.0 => false,
                default => null,
            };
        }

        if (is_string($value)) {
            return match (strtolower(trim($value))) {
                '1',
                'true',
                'on',
                'aktif',
                'nyala' => true,

                '0',
                'false',
                'off',
                'tidak aktif',
                'mati' => false,

                default => null,
            };
        }

        return null;
    }

    private function disconnectListener(
        mixed $mqtt
    ): void {
        /*
         * Gunakan manager Laravel agar koneksi bernama listener
         * dilepas dari penyimpanan koneksi paket.
         */
        try {
            MQTT::disconnect(
                self::LISTENER_CONNECTION
            );

            return;
        } catch (Throwable) {
            // Gunakan koneksi langsung sebagai fallback.
        }

        try {
            if (is_object($mqtt)) {
                $mqtt->disconnect();
            }
        } catch (Throwable) {
            // Koneksi kemungkinan sudah ditutup broker.
        }
    }

    private function acquireProcessLock(): bool
    {
        $directory = storage_path('framework');

        if (
            ! is_dir($directory)
            && ! mkdir($directory, 0775, true)
            && ! is_dir($directory)
        ) {
            Log::error(
                'Direktori lock listener MQTT tidak dapat dibuat.',
                [
                    'directory' => $directory,
                ]
            );

            return false;
        }

        $lockPath = $directory
            . DIRECTORY_SEPARATOR
            . 'smartvolt-mqtt-listener.lock';

        $handle = fopen($lockPath, 'c+');

        if ($handle === false) {
            Log::error(
                'File lock listener MQTT tidak dapat dibuka.',
                [
                    'path' => $lockPath,
                ]
            );

            return false;
        }

        if (! flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            return false;
        }

        ftruncate($handle, 0);
        rewind($handle);

        fwrite(
            $handle,
            (string) (getmypid() ?: 'unknown')
        );

        fflush($handle);

        $this->processLockHandle = $handle;

        return true;
    }

    private function releaseProcessLock(): void
    {
        if (! is_resource($this->processLockHandle)) {
            return;
        }

        try {
            flock(
                $this->processLockHandle,
                LOCK_UN
            );
        } finally {
            fclose($this->processLockHandle);
            $this->processLockHandle = null;
        }
    }

    private function messageForLog(
        string $message
    ): string {
        return substr(
            $message,
            0,
            2000
        );
    }
}