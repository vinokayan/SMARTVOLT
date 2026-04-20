<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email,' . Auth::id()],
        ]);

        Auth::user()->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], Auth::user()->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama salah.',
            ]);
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Password berhasil diperbarui.');
    }
}