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
            'floor_name' => 'Lantai 1',
            'room_number' => '101',
            'rent_amount' => 1200000,
            'status' => 'active',
        ]);

        $occupiedRoom = Room::create([
            'location_id' => $location->id,
            'floor_name' => 'Lantai 1',
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
        $response->assertSee('101');
        $response->assertSee('Tersedia');
        $response->assertSee('102');
        $response->assertSee('Terisi');

        // Critical Privacy Invariant: Tenant personal names must NEVER be exposed
        $response->assertDontSee('Rahasia Pribadi');
        $response->assertDontSee('081299998888');
    }
}
