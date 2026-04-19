<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\DeviceController;

/*
|--------------------------------------------------------------------------
| Redirect awal
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Guest (belum login)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');
});

/*
|--------------------------------------------------------------------------
| Auth (sudah login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // 🔥 Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    // 🏠 Rooms
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms');

    // ⚡ Devices
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices');

    // 🔘 Toggle Device
    Route::post('/devices/{id}/toggle', [DeviceController::class, 'toggle'])->name('devices.toggle');

    // 🚪 Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});