<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Room;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    private function ensureAdvancedMode()
    {
        if (! session('advanced_mode')) {
            abort(403, 'Mode Lanjutan belum aktif.');
        }
    }

    public function index()
    {
        $user = User::findOrFail(Auth::id());

        $rooms = Room::where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        $devices = Device::with('room')
            ->whereHas('room', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('name')
            ->get();

        $systemSetting = SystemSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'electricity_tariff' => 1444,
                'power_limit' => 900,
                'refresh_interval' => 5,
            ]
        );

        $selectedDevice = null;

        if ($systemSetting->device_id) {
            $selectedDevice = $devices->firstWhere('id', $systemSetting->device_id);
        }

        if (! $selectedDevice) {
            $selectedDevice = $devices->first();
        }

        $latestLog = null;

        if ($selectedDevice && method_exists($selectedDevice, 'energyLogs')) {
            $latestLog = $selectedDevice->energyLogs()
                ->latest()
                ->first();
        }

        return view('settings.index', compact(
            'user',
            'rooms',
            'devices',
            'systemSetting',
            'selectedDevice',
            'latestLog'
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

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        return back()->with('status', 'Akun berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
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

        $user = User::findOrFail(Auth::id());

        $deviceId = $request->input('device_id');

        $device = null;

        if ($deviceId) {
            $device = Device::where('id', $deviceId)
                ->whereHas('room', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->first();
        }

        $validated = $request->validate([
            'device_id' => ['nullable', 'integer'],
            'room_id' => [
                'required',
                Rule::exists('rooms', 'id')->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }),
            ],
            'device_name' => ['required', 'string', 'max:100'],
            'device_type' => ['nullable', 'string', 'max:100'],
            'esp32_device_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('devices', 'esp32_device_id')->ignore($device?->id),
            ],
            'esp_unit_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('devices', 'esp_unit_id')->ignore($device?->id),
            ],
            'electricity_tariff' => ['required', 'numeric', 'min:0'],
            'power_limit' => ['required', 'integer', 'min:1'],
            'refresh_interval' => ['required', 'integer', 'min:1', 'max:60'],
        ], [
            'room_id.required' => 'Ruangan wajib dipilih.',
            'room_id.exists' => 'Ruangan tidak valid.',
            'device_name.required' => 'Nama perangkat wajib diisi.',
            'device_name.max' => 'Nama perangkat maksimal 100 karakter.',
            'esp32_device_id.unique' => 'Kode device / relay sudah digunakan.',
            'esp_unit_id.unique' => 'Kode pengukur listrik sudah digunakan.',
            'electricity_tariff.required' => 'Tarif listrik wajib diisi.',
            'electricity_tariff.numeric' => 'Tarif listrik harus berupa angka.',
            'power_limit.required' => 'Batas daya wajib diisi.',
            'power_limit.integer' => 'Batas daya harus berupa angka.',
            'refresh_interval.required' => 'Interval refresh wajib diisi.',
            'refresh_interval.integer' => 'Interval refresh harus berupa angka.',
            'refresh_interval.max' => 'Interval refresh maksimal 60 detik.',
        ]);

        if (! $device) {
            $device = Device::create([
                'room_id' => $validated['room_id'],
                'name' => $validated['device_name'],
                'type' => $validated['device_type'] ?? 'other',
                'esp32_device_id' => $validated['esp32_device_id'] ?? null,
                'esp_unit_id' => $validated['esp_unit_id'] ?? null,
                'status' => false,
            ]);
        } else {
            $device->update([
                'room_id' => $validated['room_id'],
                'name' => $validated['device_name'],
                'type' => $validated['device_type'] ?? $device->type,
                'esp32_device_id' => $validated['esp32_device_id'] ?? null,
                'esp_unit_id' => $validated['esp_unit_id'] ?? null,
            ]);
        }

        SystemSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'device_id' => $device->id,
                'electricity_tariff' => $validated['electricity_tariff'],
                'power_limit' => $validated['power_limit'],
                'refresh_interval' => $validated['refresh_interval'],
            ]
        );

        return back()
            ->with('status', 'Pengaturan teknis berhasil diperbarui.')
            ->with('open_advanced_panel', true)
            ->with('selected_room_id', $device->room_id);
    }
}