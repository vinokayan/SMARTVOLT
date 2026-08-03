<?php

declare(strict_types=1);

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Repositories\MemoryRepository;

/*
|--------------------------------------------------------------------------
| Helper konfigurasi
|--------------------------------------------------------------------------
|
| Variabel opsional dari .env dinormalisasi menjadi null ketika kosong.
| Ini penting karena php-mqtt/client tidak menerima username kosong atau
| username yang hanya berisi spasi.
|
*/

$nullableEnvString = static function (string $key): ?string {
    $value = env($key);

    if ($value === null) {
        return null;
    }

    $value = trim((string) $value);

    return $value !== '' ? $value : null;
};

/*
|--------------------------------------------------------------------------
| Konfigurasi bersama
|--------------------------------------------------------------------------
*/

$mqttHost = $nullableEnvString('MQTT_HOST') ?? '127.0.0.1';

$mqttPort = max(
    1,
    min(
        65535,
        (int) env('MQTT_PORT', 1883)
    )
);

$mqttLoggingEnabled = (bool) env(
    'MQTT_ENABLE_LOGGING',
    false
);

$mqttLogChannel = $nullableEnvString(
    'MQTT_LOG_CHANNEL'
);

$listenerClientId = $nullableEnvString(
    'MQTT_LISTENER_CLIENT_ID'
) ?? 'smartvolt-laravel-listener-main';

/*
|--------------------------------------------------------------------------
| Autentikasi MQTT
|--------------------------------------------------------------------------
|
| Ketika username tidak tersedia, username dan password harus null.
| Jangan mengirim string kosong ke library MQTT.
|
*/

$mqttUsername = $nullableEnvString(
    'MQTT_AUTH_USERNAME'
);

$mqttPassword = null;

if ($mqttUsername !== null) {
    $password = env('MQTT_AUTH_PASSWORD');

    $mqttPassword = $password !== null
        ? (string) $password
        : null;
}

$authenticationSettings = [
    'username' => $mqttUsername,
    'password' => $mqttPassword,
];

/*
|--------------------------------------------------------------------------
| TLS
|--------------------------------------------------------------------------
*/

$tlsSettings = [
    'enabled' => (bool) env(
        'MQTT_TLS_ENABLED',
        false
    ),

    'allow_self_signed_certificate' => (bool) env(
        'MQTT_TLS_ALLOW_SELF_SIGNED_CERT',
        false
    ),

    'verify_peer' => (bool) env(
        'MQTT_TLS_VERIFY_PEER',
        true
    ),

    'verify_peer_name' => (bool) env(
        'MQTT_TLS_VERIFY_PEER_NAME',
        true
    ),

    'ca_file' => $nullableEnvString(
        'MQTT_TLS_CA_FILE'
    ),

    'ca_path' => $nullableEnvString(
        'MQTT_TLS_CA_PATH'
    ),

    'client_certificate_file' => $nullableEnvString(
        'MQTT_TLS_CLIENT_CERT_FILE'
    ),

    'client_certificate_key_file' => $nullableEnvString(
        'MQTT_TLS_CLIENT_CERT_KEY_FILE'
    ),

    'client_certificate_key_passphrase' => $nullableEnvString(
        'MQTT_TLS_CLIENT_KEY_PASSPHRASE'
    ),

    'alpn' => $nullableEnvString(
        'MQTT_TLS_ALPN'
    ),
];

/*
|--------------------------------------------------------------------------
| Last Will Laravel
|--------------------------------------------------------------------------
|
| Last Will untuk status alat ditangani oleh ESP32. Koneksi Laravel tidak
| perlu menerbitkan status perangkat ketika prosesnya terputus.
|
*/

$disabledLastWill = [
    'topic' => null,
    'message' => null,
    'quality_of_service' => 0,
    'retain' => false,
];

/*
|--------------------------------------------------------------------------
| Pengaturan publisher
|--------------------------------------------------------------------------
*/

$publisherConnectionSettings = [
    'tls' => $tlsSettings,
    'auth' => $authenticationSettings,
    'last_will' => $disabledLastWill,

    'connect_timeout' => max(
        1,
        (int) env(
            'MQTT_PUBLISHER_CONNECT_TIMEOUT',
            10
        )
    ),

    'socket_timeout' => max(
        1,
        (int) env(
            'MQTT_PUBLISHER_SOCKET_TIMEOUT',
            15
        )
    ),

    'resend_timeout' => max(
        1,
        (int) env(
            'MQTT_PUBLISHER_RESEND_TIMEOUT',
            5
        )
    ),

    'keep_alive_interval' => max(
        1,
        (int) env(
            'MQTT_PUBLISHER_KEEP_ALIVE_INTERVAL',
            15
        )
    ),

    /*
     * Publisher digunakan singkat saat website mengirim perintah.
     * Request berikutnya dapat membuat koneksi baru jika diperlukan.
     */
    'auto_reconnect' => [
        'enabled' => false,
        'max_reconnect_attempts' => 1,
        'delay_between_reconnect_attempts' => 0,
    ],
];

/*
|--------------------------------------------------------------------------
| Pengaturan listener
|--------------------------------------------------------------------------
*/

$listenerConnectionSettings = [
    'tls' => $tlsSettings,
    'auth' => $authenticationSettings,
    'last_will' => $disabledLastWill,

    'connect_timeout' => max(
        1,
        (int) env(
            'MQTT_LISTENER_CONNECT_TIMEOUT',
            10
        )
    ),

    /*
     * Listener adalah proses jangka panjang.
     * Socket timeout dibuat lebih panjang daripada keep-alive.
     */
    'socket_timeout' => max(
        1,
        (int) env(
            'MQTT_LISTENER_SOCKET_TIMEOUT',
            60
        )
    ),

    'resend_timeout' => max(
        1,
        (int) env(
            'MQTT_LISTENER_RESEND_TIMEOUT',
            10
        )
    ),

    'keep_alive_interval' => max(
        1,
        (int) env(
            'MQTT_LISTENER_KEEP_ALIVE_INTERVAL',
            15
        )
    ),

    /*
     * MQTTListen.php sudah menangani reconnect menggunakan
     * perulangan sendiri.
     */
    'auto_reconnect' => [
        'enabled' => false,
        'max_reconnect_attempts' => 1,
        'delay_between_reconnect_attempts' => 0,
    ],
];

return [

    /*
    |--------------------------------------------------------------------------
    | Default MQTT Connection
    |--------------------------------------------------------------------------
    |
    | Koneksi default digunakan oleh publisher website.
    | Listener wajib memakai MQTT::connection('listener').
    |
    */

    'default_connection' => 'publisher',

    /*
    |--------------------------------------------------------------------------
    | MQTT Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        /*
        |--------------------------------------------------------------------------
        | Publisher
        |--------------------------------------------------------------------------
        |
        | Client ID dibiarkan null agar library menghasilkan Client ID acak.
        | Dengan demikian koneksi website tidak mengambil alih listener.
        |
        */

        'publisher' => [
            'host' => $mqttHost,
            'port' => $mqttPort,

            'protocol' => MqttClient::MQTT_3_1,

            'client_id' => null,

            'use_clean_session' => true,

            'enable_logging' => $mqttLoggingEnabled,

            'log_channel' => $mqttLogChannel,

            'repository' => MemoryRepository::class,

            'connection_settings' => $publisherConnectionSettings,
        ],

        /*
        |--------------------------------------------------------------------------
        | Listener
        |--------------------------------------------------------------------------
        |
        | Digunakan oleh php artisan mqtt:listen untuk menerima status
        | dan ACK dari ESP32.
        |
        */

        'listener' => [
            'host' => $mqttHost,
            'port' => $mqttPort,

            'protocol' => MqttClient::MQTT_3_1,

            'client_id' => $listenerClientId,

            'use_clean_session' => (bool) env(
                'MQTT_LISTENER_CLEAN_SESSION',
                true
            ),

            'enable_logging' => $mqttLoggingEnabled,

            'log_channel' => $mqttLogChannel,

            'repository' => MemoryRepository::class,

            'connection_settings' => $listenerConnectionSettings,
        ],
    ],
];