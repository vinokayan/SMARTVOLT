<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\EnergyMeter;
use App\Models\Room;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    private function ensureAdvancedMode(): void
    {
        if (! session('advanced_mode')) {
            abort(403, 'Mode Lanjutan belum aktif.');
        }
    }

    public function index()
    {
        $user = User::findOrFail(Auth::id());

        $rooms = Room::query()
            ->withCount([
                'devices',
                'energyMeters',
            ])
            ->with([
                'devices' => fn ($query) => $query
                    ->orderBy('esp_unit_id')
                    ->orderBy('relay_code')
                    ->orderBy('name'),

                'energyMeters' => fn ($query) => $query
                    ->withCount('readings')
                    ->orderBy('esp_unit_id')
                    ->orderBy('meter_code'),
            ])
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        $devices = Device::query()
            ->with('room')
            ->whereHas('room', fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();

        $energyMeters = EnergyMeter::query()
            ->with('room')
            ->withCount('readings')
            ->where('user_id', $user->id)
            ->orderBy('room_id')
            ->orderBy('esp_unit_id')
            ->orderBy('meter_code')
            ->get();

        $systemSetting = SystemSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'electricity_tariff' => 1444,
                'power_limit' => 900,
                'refresh_interval' => 5,
            ]
        );

        return view('settings.index', compact(
            'user',
            'rooms',
            'devices',
            'energyMeters',
            'systemSetting'
        ));
    }

    public function updateProfile(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
        ]);

        $user->update($validated);

        return back()->with('status', 'Akun berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini salah.',
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Password berhasil diperbarui.');
    }

    public function updateSystem(Request $request)
    {
        $this->ensureAdvancedMode();

        $validated = $request->validate([
            'electricity_tariff' => ['required', 'numeric', 'min:0'],
            'power_limit' => ['required', 'integer', 'min:1'],
            'refresh_interval' => ['required', 'integer', 'min:1', 'max:60'],
        ], [
            'electricity_tariff.required' => 'Tarif listrik wajib diisi.',
            'electricity_tariff.numeric' => 'Tarif listrik harus berupa angka.',
            'power_limit.required' => 'Batas daya wajib diisi.',
            'power_limit.integer' => 'Batas daya harus berupa angka.',
            'refresh_interval.required' => 'Interval refresh wajib diisi.',
            'refresh_interval.integer' => 'Interval refresh harus berupa angka.',
            'refresh_interval.max' => 'Interval refresh maksimal 60 detik.',
        ]);

        SystemSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return back()
            ->with('status', 'Pengaturan sistem berhasil diperbarui.')
            ->with('open_advanced_panel', true);
    }
}