<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Konfigurasi layanan pihak ketiga yang digunakan oleh aplikasi.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env(
                'SLACK_BOT_USER_OAUTH_TOKEN'
            ),

            'channel' => env(
                'SLACK_BOT_USER_DEFAULT_CHANNEL'
            ),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth
    |--------------------------------------------------------------------------
    |
    | Digunakan oleh Laravel Socialite untuk login dan registrasi
    | menggunakan akun Google.
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),

        'client_secret' => env('GOOGLE_CLIENT_SECRET'),

        'redirect' => env(
            'GOOGLE_REDIRECT_URI',
            'http://127.0.0.1:8000/auth/google/callback'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | SmartVolt IoT Service
    |--------------------------------------------------------------------------
    |
    | API key untuk komunikasi ESP32 dengan backend Laravel.
    |
    */

    'iot' => [
        'api_key' => env('IOT_API_KEY'),
    ],

];