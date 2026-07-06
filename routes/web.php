<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EnergyController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AdvancedModeController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Default Route
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
| Hanya untuk user yang belum login.
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.process');

    Route::post('/login-post', [AuthController::class, 'login'])
        ->name('login.post');

    /*
    |--------------------------------------------------------------------------
    | Register
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
    | Forgot Password
    |--------------------------------------------------------------------------
    */

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])
        ->name('password.request');

    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('password.email');

    /*
    |--------------------------------------------------------------------------
    | Reset Password
    |--------------------------------------------------------------------------
    */

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])
        ->name('password.reset');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
| Hanya untuk user yang sudah login.
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
    | Rooms / Ruangan
    |--------------------------------------------------------------------------
    */

    Route::get('/rooms', [RoomController::class, 'index'])
        ->name('rooms');

    Route::post('/rooms', [RoomController::class, 'store'])
        ->name('rooms.store');

    Route::put('/rooms/{room}', [RoomController::class, 'update'])
        ->name('rooms.update');

    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])
        ->name('rooms.destroy');

    /*
    |--------------------------------------------------------------------------
    | Devices / Perangkat
    |--------------------------------------------------------------------------
    | Route ini dibuat fleksibel:
    | - Dari dashboard bisa pakai route('devices.store', $room->id)
    | - Dari settings bisa pakai route('devices.store') dengan input room_id
    |--------------------------------------------------------------------------
    */

    Route::get('/devices', [DeviceController::class, 'index'])
        ->name('devices');

    Route::post('/devices/{room?}', [DeviceController::class, 'store'])
        ->name('devices.store');

    Route::put('/devices/{device}', [DeviceController::class, 'update'])
        ->name('devices.update');

    Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])
        ->name('devices.destroy');

    Route::post('/devices/{device}/toggle', [DeviceController::class, 'toggle'])
        ->name('devices.toggle');

    /*
    |--------------------------------------------------------------------------
    | Energy History
    |--------------------------------------------------------------------------
    */

    Route::get('/energy-history', [EnergyController::class, 'index'])
        ->name('energy.history');

    Route::get('/energy-history/export', [EnergyController::class, 'export'])
        ->name('energy.history.export');

    /*
    |--------------------------------------------------------------------------
    | Settings
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
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    /*
    |--------------------------------------------------------------------------
    | Advanced Mode
    |--------------------------------------------------------------------------
    | Tetap disediakan agar file lama yang masih memanggil mode lanjutan
    | tidak langsung error.
    |--------------------------------------------------------------------------
    */

    Route::post('/mode-lanjutan/aktif', [AdvancedModeController::class, 'enable'])
        ->name('advanced-mode.enable');

    Route::post('/mode-lanjutan/nonaktif', [AdvancedModeController::class, 'disable'])
        ->name('advanced-mode.disable');

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});