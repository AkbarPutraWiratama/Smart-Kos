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
}
