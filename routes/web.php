<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EnergyController;
use App\Http\Controllers\SettingsController;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    // LOGIN
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

    // route utama login
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');

    // route tambahan supaya file blade yang memanggil route('login.post') tidak error
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // REGISTER
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');

    // route tambahan kalau ada blade yang memanggil route('register.post')
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // FORGOT PASSWORD
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    // RESET PASSWORD
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    // ROOMS
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

    // DEVICES
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
    Route::put('/devices/{device}', [DeviceController::class, 'update'])->name('devices.update');
    Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');
    Route::post('/devices/{device}/toggle', [DeviceController::class, 'toggle'])->name('devices.toggle');

    // ENERGY
    Route::get('/energy-history', [EnergyController::class, 'index'])->name('energy.history');

   // SETTINGS
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
Route::put('/settings/system', [SettingsController::class, 'updateSystem'])->name('settings.system.update');
    // LOGOUT
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});