<?php

namespace App\Http\Controllers;

use App\Models\SmartvoltNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead(Request $request, SmartvoltNotification $notification)
    {
        if ((int) $notification->user_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak punya akses ke notifikasi ini.');
        }

        $notification->markAsRead();

        return back()->with('status', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead(Request $request)
    {
        SmartvoltNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return back()->with('status', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
