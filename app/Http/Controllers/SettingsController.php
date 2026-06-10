<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Room;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

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

        if (!$selectedDevice) {
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
            'systemSetting',
            'selectedDevice',
            'latestLog'
        ));
    }

   public function updateProfile(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
    ]);

    $user = User::findOrFail(Auth::id());

    $user->update([
        'name' => $validated['name'],
        'email' => $validated['email'],
    ]);

    return redirect()->back()->with('success', 'Profile berhasil diperbarui.');
}

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
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

    public function updateSystem(Request $request)
    {
        $user = Auth::user();

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
            'esp32_device_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('devices', 'esp32_device_id')->ignore($device?->id),
            ],
            'electricity_tariff' => ['required', 'numeric', 'min:0'],
            'power_limit' => ['required', 'integer', 'min:1'],
            'refresh_interval' => ['required', 'integer', 'min:1', 'max:60'],
        ], [
            'room_id.required' => 'Room wajib dipilih.',
            'device_name.required' => 'Nama device wajib diisi.',
            'esp32_device_id.unique' => 'ESP32 Device ID sudah digunakan.',
            'electricity_tariff.required' => 'Tarif listrik wajib diisi.',
            'power_limit.required' => 'Power limit wajib diisi.',
            'refresh_interval.required' => 'Refresh interval wajib diisi.',
            'refresh_interval.max' => 'Refresh interval maksimal 60 detik.',
        ]);

        if (!$device) {
            $device = Device::create([
                'room_id' => $validated['room_id'],
                'name' => $validated['device_name'],
                'esp32_device_id' => $validated['esp32_device_id'] ?? null,
                'status' => false,
            ]);
        } else {
            $device->update([
                'room_id' => $validated['room_id'],
                'name' => $validated['device_name'],
                'esp32_device_id' => $validated['esp32_device_id'] ?? null,
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

        return back()->with('status', 'System settings berhasil diperbarui.');
    }
}