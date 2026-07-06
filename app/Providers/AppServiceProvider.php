<?php

namespace App\Providers;

use App\Models\SmartvoltNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer([
            'dashboard',
            'rooms',
            'settings.index',
            'auth.energy-history',
        ], function ($view) {
            $notifications = collect();
            $unreadNotificationsCount = 0;

            if (Auth::check() && Schema::hasTable('smartvolt_notifications')) {
                $notifications = SmartvoltNotification::where('user_id', Auth::id())
                    ->latest()
                    ->take(6)
                    ->get();

                $unreadNotificationsCount = SmartvoltNotification::where('user_id', Auth::id())
                    ->unread()
                    ->count();
            }

            $view->with([
                'smartvoltNotifications' => $notifications,
                'smartvoltUnreadNotificationsCount' => $unreadNotificationsCount,
            ]);
        });
    }
}
