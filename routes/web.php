<?php

use App\Http\Controllers\AdvancedModeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EnergyController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TechnicianPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Awal
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Route untuk Pengguna yang Belum Login
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.process');

    Route::post('/login-post', [AuthController::class, 'login'])
        ->name('login.post');

    /*
    |--------------------------------------------------------------------------
    | Login dengan Google
    |--------------------------------------------------------------------------
    */

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])
        ->name('google.redirect');

    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
        ->name('google.callback');

    /*
    |--------------------------------------------------------------------------
    | Registrasi
    |--------------------------------------------------------------------------
    */

    Route::get('/register', [AuthController::class, 'showRegisterForm'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.process');

    Route::post('/register-post', [AuthController::class, 'register'])
        ->name('register.post');

    /*
    |--------------------------------------------------------------------------
    | Lupa dan Reset Password
    |--------------------------------------------------------------------------
    */

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])
        ->name('password.request');

    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])
        ->name('password.reset');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Route untuk Pengguna yang Sudah Login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/data', [DashboardController::class, 'data'])
        ->name('dashboard.data');

    /*
    |--------------------------------------------------------------------------
    | Ruangan
    |--------------------------------------------------------------------------
    */

    Route::get('/rooms', [RoomController::class, 'index'])
        ->name('rooms');

    Route::get('/rooms/data', [RoomController::class, 'data'])
        ->name('rooms.data');

    Route::post('/rooms', [RoomController::class, 'store'])
        ->middleware('advanced.mode')
        ->name('rooms.store');

    Route::put('/rooms/{room}', [RoomController::class, 'update'])
        ->middleware('advanced.mode')
        ->name('rooms.update');

    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])
        ->middleware('advanced.mode')
        ->name('rooms.destroy');

    /*
    |--------------------------------------------------------------------------
    | Perangkat
    |--------------------------------------------------------------------------
    */

    Route::get('/devices', [DeviceController::class, 'index'])
        ->name('devices');

    Route::post('/devices/{room?}', [DeviceController::class, 'store'])
        ->middleware('advanced.mode')
        ->name('devices.store');

    Route::put('/devices/{device}', [DeviceController::class, 'update'])
        ->middleware('advanced.mode')
        ->name('devices.update');

    Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])
        ->middleware('advanced.mode')
        ->name('devices.destroy');

    Route::post('/devices/{device}/toggle', [DeviceController::class, 'toggle'])
        ->name('devices.toggle');

    /*
    |--------------------------------------------------------------------------
    | Riwayat Pemakaian Energi
    |--------------------------------------------------------------------------
    */

    Route::get('/energy-history', [EnergyController::class, 'index'])
        ->name('energy.history');

    Route::get('/energy-history/export', [EnergyController::class, 'export'])
        ->name('energy.history.export');

    /*
    |--------------------------------------------------------------------------
    | Pengaturan
    |--------------------------------------------------------------------------
    */

    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('settings');

    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])
        ->name('settings.profile.update');

    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])
        ->name('settings.password.update');

    Route::put('/settings/system', [SettingsController::class, 'updateSystem'])
        ->name('settings.system.update');

    /*
    |--------------------------------------------------------------------------
    | Mode Teknisi
    |--------------------------------------------------------------------------
    */

    Route::get('/technician', [TechnicianPageController::class, 'index'])
        ->name('technician.index');

    Route::prefix('technician')
        ->name('technician.')
        ->middleware('advanced.mode')
        ->group(function () {
            Route::post(
                '/rooms/{room}/sensor-listrik',
                [TechnicianPageController::class, 'storeSensorWithRelays']
            )->name('rooms.sensor.store');

            Route::post(
                '/rooms/{room}/relay',
                [TechnicianPageController::class, 'storeRelay']
            )->name('rooms.relay.store');

            Route::put(
                '/sensors/{energyMeter}',
                [TechnicianPageController::class, 'updateSensor']
            )->name('sensors.update');

            Route::patch(
                '/sensors/{energyMeter}/toggle',
                [TechnicianPageController::class, 'toggleSensor']
            )->name('sensors.toggle');

            Route::delete(
                '/sensors/{energyMeter}',
                [TechnicianPageController::class, 'destroySensor']
            )->name('sensors.destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Notifikasi
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/notifications/{notification}/read',
        [NotificationController::class, 'markAsRead']
    )->name('notifications.read');

    Route::patch(
        '/notifications/read-all',
        [NotificationController::class, 'markAllAsRead']
    )->name('notifications.read-all');

    /*
    |--------------------------------------------------------------------------
    | Akses Mode Teknisi
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/mode-lanjutan/aktif',
        [AdvancedModeController::class, 'enable']
    )->name('advanced-mode.enable');

    Route::post(
        '/mode-lanjutan/nonaktif',
        [AdvancedModeController::class, 'disable']
    )->name('advanced-mode.disable');

    Route::post(
        '/mode-lanjutan/keluar',
        [AdvancedModeController::class, 'leave']
    )->name('advanced-mode.leave');

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
