<?php

use App\Http\Controllers\Api\EnergyApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SMARTVOLT API Routes
|--------------------------------------------------------------------------
|
| Semua route dalam file ini otomatis menggunakan prefix:
|
| /api
|
| Endpoint utama:
|
| POST /api/iot/telemetry
| GET  /api/iot/esp/{esp_unit_id}/commands
| POST /api/iot/esp/{esp_unit_id}/ack
| GET  /api/energy/history/nilm
|
*/


Route::middleware('throttle:120,1')->group(function () {


    /*
    |--------------------------------------------------------------------------
    | IoT Telemetry API
    |--------------------------------------------------------------------------
    |
    | Digunakan ESP32 + PZEM untuk mengirim data energi.
    |
    */


    Route::post(
        '/iot/telemetry',
        [EnergyApiController::class, 'store']
    )
    ->name('api.iot.telemetry');



    /*
    |--------------------------------------------------------------------------
    | ESP Relay Command API
    |--------------------------------------------------------------------------
    |
    | ESP mengambil perintah relay dari server.
    |
    */


    Route::get(
        '/iot/esp/{esp_unit_id}/commands',
        [EnergyApiController::class, 'commands']
    )
    ->name('api.iot.esp.commands');



    /*
    |--------------------------------------------------------------------------
    | ESP Relay Confirmation API
    |--------------------------------------------------------------------------
    |
    | ESP mengirim status setelah relay berhasil diterapkan.
    |
    */


    Route::post(
        '/iot/esp/{esp_unit_id}/ack',
        [EnergyApiController::class, 'acknowledge']
    )
    ->name('api.iot.esp.ack');



    /*
    |--------------------------------------------------------------------------
    | Legacy API
    |--------------------------------------------------------------------------
    |
    | Dipertahankan untuk kompatibilitas firmware lama.
    |
    */


    Route::post(
        '/energy/store',
        [EnergyApiController::class, 'store']
    )
    ->name('api.energy.store');


    Route::get(
        '/device/{esp32_device_id}/command',
        [EnergyApiController::class, 'command']
    )
    ->name('api.device.command');


    Route::get(
        '/unit/{esp32_device_id}/commands',
        [EnergyApiController::class, 'commands']
    )
    ->name('api.unit.commands');



    /*
    |--------------------------------------------------------------------------
    | Energy History + NILM Dashboard API
    |--------------------------------------------------------------------------
    |
    | Mengambil histori energi beserta hasil prediksi NILM.
    |
    | Contoh:
    |
    | Power:
    | 22 W
    |
    | Detected:
    | Lampu
    |
    | Confidence:
    | 75%
    |
    */


    Route::get(
        '/energy/history/nilm',
        [EnergyApiController::class, 'historyWithNilm']
    )
    ->name('api.energy.history.nilm');


});