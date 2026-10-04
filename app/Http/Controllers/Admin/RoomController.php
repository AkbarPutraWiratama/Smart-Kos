<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoomRequest;
use App\Http\Requests\Admin\SensitiveActionConfirmRequest;
use App\Models\Location;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function store(Location $location, RoomRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['location_id'] = $location->id;

        // Check uniqueness of room_number within location & floor
        $exists = $location->rooms()
            ->where('floor_name', $data['floor_name'])
            ->where('room_number', $data['room_number'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['room_number' => 'Nomor kamar sudah ada di lantai ini.'])->withInput();
        }

        $location->rooms()->create($data);

        return redirect()->route('admin.locations.show', $location)
            ->with('success', 'Kamar berhasil ditambahkan.');
    }

    public function edit(Location $location, Room $room): View
    {
        return view('admin.rooms.edit', compact('location', 'room'));
    }

    public function update(Location $location, Room $room, RoomRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Check uniqueness excluding current room
        $exists = $location->rooms()
            ->where('floor_name', $data['floor_name'])
            ->where('room_number', $data['room_number'])
            ->whereNot('id', $room->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nomor_kamar' => 'Nomor kamar sudah ada di lantai ini.'])->withInput();
        }

        $room->update($data);

        return redirect()->route('admin.locations.show', $location)
            ->with('success', 'Informasi kamar berhasil diperbarui.');
    }

    public function destroy(SensitiveActionConfirmRequest $request, Location $location, Room $room): RedirectResponse
    {
        // Invariant: Cannot delete room if occupied
        if ($room->isOccupied()) {
            return back()->withErrors(['password' => 'Kamar tidak dapat dihapus karena masih ada penyewa aktif.']);
        }

        $roomName = $room->room_number;
        $room->delete();

        return redirect()->route('admin.locations.show', $location)
            ->with('success', 'Kamar ' . $roomName . ' berhasil dihapus.');
    }
}