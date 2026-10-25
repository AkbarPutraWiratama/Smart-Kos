<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoomStatusVisualizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'     => 'admin',
            'status'   => 'active',
            'password' => Hash::make('adminpassword'),
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Grand Surya',
            'address'     => 'Jl. Surya Utama No. 10',
            'floor_count' => 2,
            'status'      => 'active',
        ]);
    }

    public function test_admin_can_view_room_status_visualization_by_floor(): void
    {
        // 2 floors x 2 rooms each
        $room101 = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 2,
            'room_number' => 201,
            'rent_amount' => 1500000,
            'status'      => 'active',
        ]);

        $room001 = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 1,
            'room_number' => 101,
            'rent_amount' => 1200000,
            'status'      => 'active',
        ]);

        // Occupy room 201 with a tenant
        $tenantUser = User::factory()->create([
            'name'     => 'Ahmad Santoso',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $profile = TenantProfile::create([
            'user_id' => $tenantUser->id,
            'phone'   => '081234567890',
        ]);

        RoomAssignment::create([
            'room_id'              => $room101->id,
            'tenant_id'            => $profile->id,
            'start_date'           => '2026-10-01',
            'status'               => 'active',
            'rent_amount_snapshot' => 1500000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.tenants.index', [
            'location_id' => $this->location->id,
            'tab'         => 'visualization',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Visualisasi Denah Kamar');
        $response->assertSee('Denah Visualisasi Kamar: Kos Grand Surya');
        $response->assertSee('Lantai 2');
        $response->assertSee('Lantai 1');

        // Check occupied room 201
        $response->assertSee('201');
        $response->assertSee('Terisi');
        $response->assertSee('Ahmad Santoso');
        $response->assertSee('081234567890');

        // Check vacant room 101
        $response->assertSee('101');
        $response->assertSee('Kosong');
        $response->assertSee('Rp 1.200.000');
        $response->assertSee('+ Tugaskan');
    }

    public function test_visualization_handles_2_floors_by_5_rooms_layout(): void
    {
        // 2 floors x 5 rooms as highlighted in specification
        for ($i = 1; $i <= 5; $i++) {
            // Floor 1: 001 .. 005
            Room::create([
                'location_id' => $this->location->id,
                'floor_name'  => 1,
                'room_number' => sprintf('%03d', $i),
                'rent_amount' => 1000000,
                'status'      => 'active',
            ]);

            // Floor 2: 101 .. 105
            Room::create([
                'location_id' => $this->location->id,
                'floor_name'  => 2,
                'room_number' => 100 + $i,
                'rent_amount' => 1200000,
                'status'      => 'active',
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.tenants.index', [
            'location_id' => $this->location->id,
            'tab'         => 'visualization',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Lantai 2');
        $response->assertSee('Lantai 1');

        // Check Floor 2 rooms
        foreach ([101, 102, 103, 104, 105] as $num) {
            $response->assertSee((string) $num);
        }

        // Check Floor 1 rooms
        foreach (['001', '002', '003', '004', '005'] as $num) {
            $response->assertSee($num);
        }
    }

    public function test_staff_detail_level_hides_financial_figures(): void
    {
        $room = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 1,
            'room_number' => 101,
            'rent_amount' => 1750000,
            'status'      => 'active',
        ]);

        $roomsByFloor = collect([1 => collect([$room])]);

        // Render component with role="staff"
        $staffHtml = Blade::render(
            '<x-smart-kos.room-visualization :roomsByFloor="$roomsByFloor" role="staff" />',
            ['roomsByFloor' => $roomsByFloor]
        );

        $this->assertStringNotContainsString('Rp 1.750.000', $staffHtml);
        $this->assertStringContainsString('101', $staffHtml);
        $this->assertStringContainsString('Kamar Tersedia', $staffHtml);

        // Render component with role="admin"
        $adminHtml = Blade::render(
            '<x-smart-kos.room-visualization :roomsByFloor="$roomsByFloor" role="admin" />',
            ['roomsByFloor' => $roomsByFloor]
        );

        $this->assertStringContainsString('Rp 1.750.000', $adminHtml);
        $this->assertStringContainsString('+ Tugaskan', $adminHtml);
    }
}

