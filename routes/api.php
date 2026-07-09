<?php

use App\Http\Controllers\Api\EnergyApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SMARTVOLT API Routes
|--------------------------------------------------------------------------
|
| Semua route pada file ini otomatis memakai prefix /api.
|
| Endpoint IoT utama:
| POST /api/iot/telemetry
| GET  /api/iot/esp/{esp_unit_id}/commands
| POST /api/iot/esp/{esp_unit_id}/ack
|
*/

Route::middleware('throttle:120,1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | IoT API Baru
    |--------------------------------------------------------------------------
    |
    | Telemetry PZEM:
    | POST /api/iot/telemetry
    |
    | ESP mengambil status relay:
    | GET /api/iot/esp/2/commands
    |
    | ESP mengirim konfirmasi setelah relay fisik diterapkan:
    | POST /api/iot/esp/2/ack
    |
    */

    Route::post('/iot/telemetry', [EnergyApiController::class, 'store'])
        ->name('api.iot.telemetry');

    Route::get('/iot/esp/{esp_unit_id}/commands', [EnergyApiController::class, 'commands'])
        ->name('api.iot.esp.commands');

    Route::post('/iot/esp/{esp_unit_id}/ack', [EnergyApiController::class, 'acknowledge'])
        ->name('api.iot.esp.ack');

    /*
    |--------------------------------------------------------------------------
    | Legacy API
    |--------------------------------------------------------------------------
    |
    | Endpoint lama tetap dipertahankan agar firmware ESP versi lama
    | tidak langsung berhenti bekerja.
    |
    */

    Route::post('/energy/store', [EnergyApiController::class, 'store'])
        ->name('api.energy.store');

    Route::get('/device/{esp32_device_id}/command', [EnergyApiController::class, 'command'])
        ->name('api.device.command');

    Route::get('/unit/{esp32_device_id}/commands', [EnergyApiController::class, 'commands'])
        ->name('api.unit.commands');
});