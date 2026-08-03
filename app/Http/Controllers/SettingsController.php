<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Throwable;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        /*
         * Tautan lama tetap diarahkan ke halaman Mode Teknisi yang
         * sekarang berdiri sendiri. Mode Teknisi tidak lagi menjadi tab
         * di halaman Pengaturan.
         */
        if ($request->query('tab') === 'technician') {
            return redirect()->route('technician.index');
        }

        $user = User::findOrFail(Auth::id());

        $systemSetting = SystemSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'electricity_tariff' => 1444,
                'power_limit' => 900,
                'refresh_interval' => 30,
            ]
        );

        return view('settings.index', [
            'user' => $user,
            'systemSetting' => $systemSetting,
            'systemConnected' => $this->systemConnected((int) $user->id),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
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

        return redirect()
            ->route('settings', ['tab' => 'profile'])
            ->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = User::findOrFail(Auth::id());

        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Masukkan kata sandi saat ini.',
            'password.required' => 'Masukkan kata sandi baru.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi belum sesuai.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return redirect()
                ->route('settings', ['tab' => 'security'])
                ->withErrors([
                    'current_password' => 'Kata sandi saat ini tidak sesuai.',
                ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('settings', ['tab' => 'security'])
            ->with('status', 'Kata sandi berhasil diperbarui.');
    }

    public function updateSystem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'electricity_tariff' => ['required', 'numeric', 'min:0'],
            'power_limit' => ['required', 'integer', 'min:1'],
            'refresh_interval' => ['required', 'integer', 'min:10', 'max:60'],
        ], [
            'electricity_tariff.required' => 'Tarif listrik wajib diisi.',
            'electricity_tariff.numeric' => 'Tarif listrik harus berupa angka.',
            'power_limit.required' => 'Batas daya wajib diisi.',
            'power_limit.integer' => 'Batas daya harus berupa angka.',
            'refresh_interval.required' => 'Waktu pembaruan wajib diisi.',
            'refresh_interval.integer' => 'Waktu pembaruan harus berupa angka.',
            'refresh_interval.min' => 'Waktu pembaruan minimal 10 detik.',
            'refresh_interval.max' => 'Waktu pembaruan maksimal 60 detik.',
        ]);

        SystemSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return redirect()
            ->route('settings', ['tab' => 'system'])
            ->with('status', 'Pengaturan listrik berhasil diperbarui.');
    }

    private function systemConnected(int $userId): bool
    {
        $threshold = now()->subMinutes(
            max(
                1,
                (int) config(
                    'services.iot.online_timeout_minutes',
                    2
                )
            )
        );

        return Device::query()
            ->whereHas(
                'room',
                fn ($query) => $query->where('user_id', $userId)
            )
            ->get()
            ->contains(function (Device $device) use ($threshold) {
                if (! (bool) ($device->is_online ?? false)) {
                    return false;
                }

                $lastSeenAt = $this->toCarbon($device->last_seen_at ?? null);

                return $lastSeenAt !== null
                    && $lastSeenAt->greaterThanOrEqualTo($threshold);
            });
    }

    private function toCarbon(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        try {
            return Carbon::parse((string) $value);
        } catch (Throwable) {
            return null;
        }
    }
}
