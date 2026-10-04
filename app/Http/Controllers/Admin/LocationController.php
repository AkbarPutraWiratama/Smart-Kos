<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LocationRequest;
use App\Http\Requests\Admin\SensitiveActionConfirmRequest;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $locations = Location::withCount(['rooms', 'rooms as active_rooms_count' => function ($q) {
            $q->where('status', 'active');
        }])->latest()->get();

        return view('admin.locations.index', compact('locations'));
    }

    public function create(): View
    {
        return view('admin.locations.create');
    }

    public function store(LocationRequest $request): RedirectResponse
    {
        $location = Location::create($request->validated());

        return redirect()->route('admin.locations.show', $location)
            ->with('success', 'Lokasi kos berhasil ditambahkan.');
    }

    public function show(Location $location): View
    {
        $location->load(['rooms' => function ($q) {
            $q->with('activeAssignment.tenantProfile.user')->orderBy('floor_name')->orderBy('room_number');
        }]);

        $roomsByFloor = $location->rooms->groupBy('floor_name');

        return view('admin.locations.show', compact('location', 'roomsByFloor'));
    }

    public function edit(Location $location): View
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(LocationRequest $request, Location $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()->route('admin.locations.show', $location)
            ->with('success', 'Informasi lokasi kos berhasil diperbarui.');
    }

    public function destroy(SensitiveActionConfirmRequest $request, Location $location): RedirectResponse
    {
        // Invariant: Cannot delete location if it has active room assignments
        $hasActiveTenants = $location->rooms()->whereHas('activeAssignment')->exists();

        if ($hasActiveTenants) {
            return back()->withErrors(['password' => 'Lokasi tidak dapat dihapus karena masih ada kamar yang sedang ditempati penyewa aktif.']);
        }

        $locationName = $location->name;
        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Lokasi ' . $locationName . ' berhasil dihapus.');
    }
}