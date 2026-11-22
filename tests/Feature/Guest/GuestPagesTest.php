<?php

namespace Tests\Feature\Guest;

use App\Models\Location;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang di Smart Kos');
        $response->assertSee('Lihat Lokasi & Kamar', false);
        $response->assertSee('Masuk');
    }

    public function test_guest_can_view_locations_and_room_availability(): void
    {
        $location = Location::create([
            'name' => 'Kos Melati',
            'address' => 'Jl. Melati No. 5',
            'floor_count' => 2,
            'status' => 'active',
        ]);

        $emptyRoom = Room::create([
            'location_id' => $location->id,
            'floor_name' => 1,
            'room_number' => '101',
            'rent_amount' => 1200000,
            'status' => 'active',
        ]);

        $occupiedRoom = Room::create([
            'location_id' => $location->id,
            'floor_name' => 1,
            'room_number' => '102',
            'rent_amount' => 1200000,
            'status' => 'active',
        ]);

        $tenantUser = User::factory()->create([
            'name' => 'Rahasia Pribadi',
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $tenantProfile = TenantProfile::create([
            'user_id' => $tenantUser->id,
            'phone' => '081299998888',
        ]);

        RoomAssignment::create([
            'room_id' => $occupiedRoom->id,
            'tenant_id' => $tenantProfile->id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'rent_amount_snapshot' => 1200000,
        ]);

        $response = $this->get('/rooms?location_id='.$location->id);

        $response->assertStatus(200);
        $response->assertSee('Kos Melati');
        $response->assertSee('Lantai 1');
        $response->assertSee('Kamar 101');
        $response->assertSee('Tersedia');
        $response->assertSee('Kamar 102');
        $response->assertSee('Terisi');

        // Critical Privacy Invariant: Tenant personal names must NEVER be exposed
        $response->assertDontSee('Rahasia Pribadi');
        $response->assertDontSee('081299998888');
    }

    public function test_guest_can_see_location_photos_carousel(): void
    {
        $location = Location::create([
            'name' => 'Kos Cantik',
            'address' => 'Jl. Mawar No. 10',
            'floor_count' => 1,
            'status' => 'active',
            'photo_path' => 'locations/legacy.jpg',
        ]);

        $location->photos()->create([
            'path' => 'locations/extra1.jpg',
        ]);

        $response = $this->get('/rooms?location_id='.$location->id);

        $response->assertStatus(200);
        $response->assertSee('legacy.jpg', false);
        $response->assertSee('extra1.jpg', false);
    }

    public function test_guest_can_see_embedded_google_map_on_rooms_page(): void
    {
        $location = Location::create([
            'name'            => 'Kos Peta Nyaman',
            'address'         => 'Jl. Peta No. 99, Yogyakarta',
            'google_maps_url' => 'https://maps.google.com/?q=-7.7828,110.3670',
            'floor_count'     => 1,
            'status'          => 'active',
        ]);

        $response = $this->get('/rooms?location_id='.$location->id);

        $response->assertStatus(200);
        $response->assertSee('Peta Lokasi');
        $response->assertSee('maps.google.com/maps?q=', false);
        $response->assertSee('-7.7828%2C110.3670', false);
        $response->assertSee('output=embed', false);
    }
}
