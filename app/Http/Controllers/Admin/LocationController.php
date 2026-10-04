<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LocationRequest;
use App\Http\Requests\Admin\SensitiveActionConfirmRequest;
use App\Models\Location;
use App\Models\LocationPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $locations = Location::with('photos')->withCount(['rooms', 'rooms as active_rooms_count' => function ($q) {
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
        $data = $request->validated();
        $photos = $request->file('photos', []) ?: [];
        unset($data['photos']);

        $location = Location::create($data);
        $this->storePhotos($location, $photos);

        return redirect()->route('admin.locations.show', $location)
            ->with('success', 'Lokasi kos berhasil ditambahkan.');
    }

    public function show(Request $request, Location $location): View
    {
        $location->load(['photos', 'rooms' => function ($q) {
            $q->with('activeAssignment.tenantProfile.user')->orderBy('floor_name', 'asc')->orderBy('room_number', 'asc');
        }]);

        $floorOrder = $request->input('floor_order') === 'desc' ? 'desc' : 'asc';
        $roomsByFloor = $location->rooms
            ->groupBy('floor_name')
            ->sortKeysUsing(function ($left, $right) use ($floorOrder) {
                $comparison = (int) $left <=> (int) $right;

                return $floorOrder === 'desc' ? -$comparison : $comparison;
            });

        return view('admin.locations.show', compact('location', 'roomsByFloor', 'floorOrder'));
    }

    public function edit(Location $location): View
    {
        $location->load('photos');

        return view('admin.locations.edit', compact('location'));
    }

    public function update(LocationRequest $request, Location $location): RedirectResponse
    {
        $data = $request->validated();
        $photos = $request->file('photos', []) ?: [];
        unset($data['photos']);

        $location->update($data);
        $this->storePhotos($location, $photos);

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
        $location->load('photos');
        Storage::disk('public')->delete($location->photos->pluck('path')->all());
        if ($location->photo_path) {
            Storage::disk('public')->delete($location->photo_path);
        }
        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Lokasi ' . $locationName . ' berhasil dihapus.');
    }

    public function destroyPhoto(SensitiveActionConfirmRequest $request, Location $location, LocationPhoto $photo): RedirectResponse
    {
        abort_unless($photo->location_id === $location->id, 404);

        $photoPath = $photo->path;
        $photo->delete();
        Storage::disk('public')->delete($photoPath);

        return back()->with('success', 'Foto lokasi berhasil dihapus.');
    }

    public function destroyLegacyPhoto(SensitiveActionConfirmRequest $request, Location $location): RedirectResponse
    {
        if (! $location->photo_path) {
            return back()->withErrors(['photo' => 'Foto lokasi tidak ditemukan.']);
        }

        $photoPath = $location->photo_path;
        $location->update(['photo_path' => null]);
        Storage::disk('public')->delete($photoPath);

        return back()->with('success', 'Foto lokasi berhasil dihapus.');
    }

    private function storePhotos(Location $location, array $photos): void
    {
        foreach ($photos as $photo) {
            $location->photos()->create([
                'path' => $photo->store('locations', 'public'),
            ]);
        }
    }
}