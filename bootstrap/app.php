<?php

use App\Http\Middleware\EnsureAdvancedMode;
use App\Http\Middleware\TrackAdvancedModeContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Mencatat kapan pengguna keluar dari Mode Teknisi dan memeriksa
         * apakah batas waktu kembali satu menit sudah berakhir.
         */
        $middleware->web(append: [
            TrackAdvancedModeContext::class,
        ]);

        /*
         * Melindungi route yang hanya boleh digunakan saat akses teknisi aktif.
         */
        $middleware->alias([
            'advanced.mode' => EnsureAdvancedMode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
