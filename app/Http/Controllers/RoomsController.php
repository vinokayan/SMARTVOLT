<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::withCount('devices')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('rooms', compact('rooms'));
    }

    public function show(Room $room)
    {
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke room ini.');
        }

        $room->load('devices');

        return view('rooms-show', [
            'room' => $room,
            'devices' => $room->devices()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama room wajib diisi.',
            'name.max' => 'Nama room maksimal 100 karakter.',
        ]);

        Room::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'slug' => $this->makeUniqueSlug($validated['name']),
            'status' => true,
        ]);

        return redirect()->route('rooms')->with('status', 'Room berhasil ditambahkan.');
    }

    public function update(Request $request, Room $room)
    {
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke room ini.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $room->update([
            'name' => $validated['name'],
            'slug' => $this->makeUniqueSlug($validated['name'], $room->id),
        ]);

        return redirect()->route('rooms')->with('status', 'Room berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke room ini.');
        }

        if ($room->devices()->count() > 0) {
            return redirect()->route('rooms')->withErrors([
                'room' => 'Room tidak bisa dihapus karena masih memiliki device.',
            ]);
        }

        $room->delete();

        return redirect()->route('rooms')->with('status', 'Room berhasil dihapus.');
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'room';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Room::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}