<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use PhpMqtt\Client\Facades\MQTT;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::with('room')
            ->whereHas('room', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->latest()
            ->get();

        return view('devices', compact('devices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices')->where(function ($query) use ($request) {
                    return $query->where('room_id', $request->room_id);
                }),
            ],
            'esp32_device_id' => [
                'required',
                'string',
                'max:100',
                'unique:devices,esp32_device_id',
            ],
            'type' => ['nullable', 'string', 'max:100'],
        ], [
            'room_id.required' => 'Room is required.',
            'room_id.exists' => 'The selected room is invalid.',
            'name.required' => 'Device name is required.',
            'name.unique' => 'Device name already exists in this room.',
            'esp32_device_id.required' => 'Device Key is required.',
            'esp32_device_id.unique' => 'Device Key is already used.',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        if ($room->user_id !== Auth::id()) {
            abort(403, 'You do not have access to this room.');
        }

        Device::create([
            'room_id' => $room->id,
            'name' => $validated['name'],
            'type' => $validated['type'] ?? null,
            'esp32_device_id' => $validated['esp32_device_id'],
            'status' => false,
        ]);

        return back()->with('status', ucfirst($validated['name']) . ' has been added successfully.');
    }

    public function update(Request $request, Device $device)
    {
        $device->load('room');

        if (!$device->room || $device->room->user_id !== Auth::id()) {
            abort(403, 'You do not have access to this device.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices')
                    ->where(function ($query) use ($device) {
                        return $query->where('room_id', $device->room_id);
                    })
                    ->ignore($device->id),
            ],
            'esp32_device_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices', 'esp32_device_id')
                    ->ignore($device->id),
            ],
            'type' => ['nullable', 'string', 'max:100'],
        ], [
            'name.required' => 'Device name is required.',
            'name.unique' => 'Device name already exists in this room.',
            'esp32_device_id.required' => 'Device Key is required.',
            'esp32_device_id.unique' => 'Device Key is already used.',
        ]);

        $device->update([
            'name' => $validated['name'],
            'esp32_device_id' => $validated['esp32_device_id'],
            'type' => $validated['type'] ?? $device->type,
        ]);

        return back()->with('status', 'Device updated successfully.');
    }

    public function destroy(Device $device)
    {
        $device->load('room');

        if (!$device->room || $device->room->user_id !== Auth::id()) {
            abort(403, 'You do not have access to this device.');
        }

        $deviceName = $device->name;

        $device->delete();

        return back()->with('status', ucfirst($deviceName) . ' has been deleted successfully.');
    }

    public function toggle(Request $request, Device $device)
    {
        $device->load('room');

        if (!$device->room || $device->room->user_id !== Auth::id()) {
            abort(403, 'You do not have access to this device.');
        }

        $newStatus = ! (bool) $device->status;

        if (!$device->esp32_device_id) {
            return back()->withErrors([
                'device' => 'This device does not have an ESP32 Device ID yet. Please fill in the Device Key first.',
            ]);
        }

        $topic = 'smartvolt/control/' . $device->esp32_device_id;

        $payload = json_encode([
            'esp32_device_id' => $device->esp32_device_id,
            'device_id' => $device->id,
            'device_name' => $device->name,
            'relay' => $newStatus,
            'status' => $newStatus ? 'ON' : 'OFF',
        ]);

        try {
            MQTT::publish($topic, $payload, 0);

            $device->update([
                'status' => $newStatus,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'status' => $device->status ? 'on' : 'off',
                    'mqtt_topic' => $topic,
                    'mqtt_payload' => json_decode($payload, true),
                ]);
            }

            return back()
                ->with(
                    'status',
                    $newStatus
                        ? $device->name . ' has been turned on successfully.'
                        : $device->name . ' has been turned off successfully.'
                )
                ->with('open_room_id', $request->open_room_id);
        } catch (\Throwable $e) {
            Log::error('Failed to publish MQTT control command', [
                'topic' => $topic,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send MQTT command. Make sure the Mosquitto broker is running.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return back()->withErrors([
                'mqtt' => 'Failed to send MQTT command. Make sure the Mosquitto broker is running.',
            ]);
        }
    }
}