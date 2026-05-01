<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EnergyApiController;

Route::post('/energy/store', [EnergyApiController::class, 'store']);
Route::get('/device/{esp32_device_id}/command', [EnergyApiController::class, 'command']);