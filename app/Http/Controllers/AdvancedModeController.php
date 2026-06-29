<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdvancedModeController extends Controller
{
    public function enable(Request $request)
    {
        $validated = $request->validate([
            'pin' => ['required', 'string'],
        ], [
            'pin.required' => 'PIN wajib diisi.',
        ]);

        $correctPin = (string) config('smartvolt.advanced_pin');

        if (! hash_equals($correctPin, (string) $validated['pin'])) {
            return back()->withErrors([
                'advanced_mode' => 'PIN Mode Lanjutan salah.',
            ]);
        }

        session([
            'advanced_mode' => true,
        ]);

        return back()
            ->with('status', 'Mode Lanjutan aktif.')
            ->with('open_advanced_panel', true);
    }

    public function disable()
    {
        session()->forget('advanced_mode');

        return back()->with('status', 'Mode Lanjutan dinonaktifkan.');
    }
}