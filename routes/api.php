<?php

use App\Http\Controllers\Api\EnergyApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SMARTVOLT API Routes
|--------------------------------------------------------------------------
|
| Semua route di file ini otomatis memakai prefix /api.
|
| Endpoint utama:
| POST /api/iot/telemetry
| GET  /api/iot/esp/{esp_unit_id}/commands
|
| Endpoint legacy tetap dipertahankan:
| POST /api/energy/store
| GET  /api/device/{esp32_device_id}/command
| GET  /api/unit/{esp32_device_id}/commands
|
*/

/*
|--------------------------------------------------------------------------
| New IoT API
|--------------------------------------------------------------------------
| Endpoint baru yang lebih jelas memakai esp_unit_id.
|
| ESP32 mengirim data sensor:
| POST /api/iot/telemetry
|
| ESP32 mengambil semua status relay:
| GET /api/iot/esp/ESP32-001/commands
|
*/
Route::post('/iot/telemetry', [EnergyApiController::class, 'store'])
    ->name('api.iot.telemetry');

Route::get('/iot/esp/{esp_unit_id}/commands', [EnergyApiController::class, 'commands'])
    ->name('api.iot.esp.commands');

/*
|--------------------------------------------------------------------------
| Legacy API
|--------------------------------------------------------------------------
| Endpoint lama tetap ada agar kode ESP32 versi lama masih berjalan.
|
*/
Route::post('/energy/store', [EnergyApiController::class, 'store'])
    ->name('api.energy.store');

Route::get('/device/{esp32_device_id}/command', [EnergyApiController::class, 'command'])
    ->name('api.device.command');

Route::get('/unit/{esp32_device_id}/commands', [EnergyApiController::class, 'commands'])
    ->name('api.unit.commands');