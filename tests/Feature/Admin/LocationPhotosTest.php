<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocationPhotosTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_locations_index_displays_photo_thumbnails(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $locationWithPhoto = Location::create([
            'name' => 'Kos Anyelir',
            'address' => 'Jl. Anyelir No. 1',
            'floor_count' => 2,
            'status' => 'active',
        ]);
        $locationWithPhoto->photos()->create([
            'path' => 'locations/thumbnail.jpg',
        ]);

        $locationWithoutPhoto = Location::create([
            'name' => 'Kos Melati',
            'address' => 'Jl. Melati No. 2',
            'floor_count' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.locations.index'));

        $response->assertStatus(200);
        $response->assertSee('storage/locations/thumbnail.jpg');
        $response->assertSee('Belum ada foto lokasi');
    }

    public function test_admin_locations_show_displays_gallery_carousel(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $location = Location::create([
            'name' => 'Kos Anggrek',
            'address' => 'Jl. Anggrek No. 8',
            'floor_count' => 2,
            'status' => 'active',
            'photo_path' => 'locations/legacy_kos.jpg',
        ]);
        $location->photos()->create([
            'path' => 'locations/photo1.jpg',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.locations.show', $location));

        $response->assertStatus(200);
        $response->assertSee('legacy_kos.jpg', false);
        $response->assertSee('photo1.jpg', false);
        $response->assertSee('Foto Kos Anggrek');
    }

    public function test_admin_can_delete_single_photo_without_password(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $location = Location::create([
            'name' => 'Kos Mawar',
            'address' => 'Jl. Mawar No. 3',
            'floor_count' => 1,
            'status' => 'active',
        ]);

        $photo = $location->photos()->create([
            'path' => 'locations/photo_to_delete.jpg',
        ]);

        Storage::disk('public')->put($photo->path, 'dummy content');

        // Delete photo without password confirmation (simple pop-up confirmation in UI)
        $response = $this->actingAs($admin)->delete(route('admin.locations.photos.destroy', [$location, $photo]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Foto lokasi berhasil dihapus.');

        $this->assertDatabaseMissing('location_photos', [
            'id' => $photo->id,
        ]);
        Storage::disk('public')->assertMissing($photo->path);
    }

    public function test_admin_can_delete_legacy_photo_without_password(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $location = Location::create([
            'name' => 'Kos Melati',
            'address' => 'Jl. Melati No. 4',
            'floor_count' => 1,
            'status' => 'active',
            'photo_path' => 'locations/legacy_delete.jpg',
        ]);

        Storage::disk('public')->put('locations/legacy_delete.jpg', 'dummy content');

        $response = $this->actingAs($admin)->delete(route('admin.locations.legacy-photo.destroy', $location));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Foto lokasi berhasil dihapus.');

        $this->assertNull($location->fresh()->photo_path);
        Storage::disk('public')->assertMissing('locations/legacy_delete.jpg');
    }

    public function test_admin_can_bulk_delete_multiple_photos(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $location = Location::create([
            'name' => 'Kos Sakura',
            'address' => 'Jl. Sakura No. 7',
            'floor_count' => 2,
            'status' => 'active',
            'photo_path' => 'locations/sakura_legacy.jpg',
        ]);

        $photo1 = $location->photos()->create(['path' => 'locations/sakura_1.jpg']);
        $photo2 = $location->photos()->create(['path' => 'locations/sakura_2.jpg']);
        $photo3 = $location->photos()->create(['path' => 'locations/sakura_3.jpg']);

        Storage::disk('public')->put('locations/sakura_legacy.jpg', 'content');
        Storage::disk('public')->put($photo1->path, 'content');
        Storage::disk('public')->put($photo2->path, 'content');
        Storage::disk('public')->put($photo3->path, 'content');

        // Bulk delete photo1, photo2, and legacy photo
        $response = $this->actingAs($admin)->delete(route('admin.locations.photos.bulk-destroy', $location), [
            'photo_ids' => [$photo1->id, $photo2->id],
            'delete_legacy' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '3 foto lokasi berhasil dihapus.');

        $this->assertDatabaseMissing('location_photos', ['id' => $photo1->id]);
        $this->assertDatabaseMissing('location_photos', ['id' => $photo2->id]);
        $this->assertDatabaseHas('location_photos', ['id' => $photo3->id]);
        $this->assertNull($location->fresh()->photo_path);

        Storage::disk('public')->assertMissing($photo1->path);
        Storage::disk('public')->assertMissing($photo2->path);
        Storage::disk('public')->assertMissing('locations/sakura_legacy.jpg');
        Storage::disk('public')->assertExists($photo3->path);
    }

    public function test_delete_location_shows_red_warning_when_has_active_tenants(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $location = Location::create([
            'name' => 'Kos Terisi',
            'address' => 'Jl. Terisi No. 9',
            'floor_count' => 1,
            'status' => 'active',
        ]);

        $room = \App\Models\Room::create([
            'location_id' => $location->id,
            'floor_name' => 1,
            'room_number' => 101,
            'rent_amount' => 1000000,
            'status' => 'active',
        ]);

        $tenantUser = User::factory()->create([
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $tenantProfile = \App\Models\TenantProfile::create([
            'user_id' => $tenantUser->id,
            'phone' => '081234567890',
        ]);

        \App\Models\RoomAssignment::create([
            'room_id' => $room->id,
            'tenant_id' => $tenantProfile->id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'rent_amount_snapshot' => 1000000,
        ]);

        // Viewing edit page should display the red warning in the modal
        $response = $this->actingAs($admin)->get(route('admin.locations.edit', $location));
        $response->assertStatus(200);
        $response->assertSee('Lokasi tidak dapat dihapus!');
        $response->assertSee('Masih ada kamar yang sedang ditempati oleh penyewa aktif');

        // Viewing show page should also display the red warning in the modal
        $response = $this->actingAs($admin)->get(route('admin.locations.show', $location));
        $response->assertStatus(200);
        $response->assertSee('Lokasi tidak dapat dihapus!');

        // Attempting to delete should fail with session error
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.locations.destroy', $location), [
            'password' => 'password',
        ]);
        $deleteResponse->assertRedirect();
        $deleteResponse->assertSessionHasErrors('password');
        $this->assertDatabaseHas('locations', ['id' => $location->id]);
    }

    public function test_occupied_room_status_cannot_be_changed_to_inactive(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $location = Location::create([
            'name' => 'Kos Cempaka',
            'address' => 'Jl. Cempaka No. 5',
            'floor_count' => 1,
            'status' => 'active',
        ]);

        $room = \App\Models\Room::create([
            'location_id' => $location->id,
            'floor_name' => 1,
            'room_number' => 201,
            'rent_amount' => 1500000,
            'status' => 'active',
        ]);

        $tenantUser = User::factory()->create([
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $tenantProfile = \App\Models\TenantProfile::create([
            'user_id' => $tenantUser->id,
            'phone' => '0899999999',
        ]);

        \App\Models\RoomAssignment::create([
            'room_id' => $room->id,
            'tenant_id' => $tenantProfile->id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'rent_amount_snapshot' => 1500000,
        ]);

        // Trying to update status to inactive should fail validation
        $response = $this->actingAs($admin)->put(route('admin.locations.rooms.update', [$location, $room]), [
            'floor_name' => 1,
            'room_number' => 201,
            'rent_amount' => 1500000,
            'status' => 'inactive',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertEquals('active', $room->fresh()->status);

        // Updating occupied room with active status succeeds
        $responseSuccess = $this->actingAs($admin)->put(route('admin.locations.rooms.update', [$location, $room]), [
            'floor_name' => 1,
            'room_number' => 201,
            'rent_amount' => 1600000,
            'status' => 'active',
            'password' => 'password',
        ]);

        $responseSuccess->assertRedirect(route('admin.locations.show', $location));
        $this->assertEquals(1600000, $room->fresh()->rent_amount);
    }
}
