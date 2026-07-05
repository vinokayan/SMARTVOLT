<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EnergyApiController;

/*
|--------------------------------------------------------------------------
| SMARTVOLT API Routes
|--------------------------------------------------------------------------
|
| Semua route di file ini otomatis memakai prefix /api.
|
| Endpoint aktif:
| POST /api/energy/store
| GET  /api/device/{esp32_device_id}/command
| GET  /api/unit/{esp32_device_id}/commands
|
*/

/*
|--------------------------------------------------------------------------
| Sensor Energy Store
|--------------------------------------------------------------------------
| Dipakai ESP32 untuk mengirim data sensor listrik ke Laravel.
|
| Contoh:
| POST /api/energy/store
|
*/
Route::post('/energy/store', [EnergyApiController::class, 'store'])
    ->name('api.energy.store');

/*
|--------------------------------------------------------------------------
| Single Relay Command
|--------------------------------------------------------------------------
| Endpoint lama. Tetap dipertahankan agar kode ESP32 versi lama
| masih bisa berjalan.
|
| Contoh:
| GET /api/device/3/command
|
*/
Route::get('/device/{esp32_device_id}/command', [EnergyApiController::class, 'command'])
    ->name('api.device.command');

/*
|--------------------------------------------------------------------------
| Multi Relay Commands
|--------------------------------------------------------------------------
| Endpoint baru untuk ESP32 yang memiliki lebih dari satu relay.
| ESP32 akan mengambil semua status relay berdasarkan kode sensor
| atau esp32_device_id yang sama.
|
| Contoh:
| GET /api/unit/3/commands
|
*/
Route::get('/unit/{esp32_device_id}/commands', [EnergyApiController::class, 'commands'])
    ->name('api.unit.commands');