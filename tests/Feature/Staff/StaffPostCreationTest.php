<?php

namespace Tests\Feature\Staff;

use App\Models\Location;
use App\Models\Post;
use App\Models\PostTarget;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffPostCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $staffUser;
    private User $otherStaffUser;
    private Location $locationA;
    private Location $locationB;
    private Room $roomA1;
    private Room $roomB1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staffUser = User::factory()->create([
            'name'     => 'Budi Santoso',
            'username' => 'budistaff',
            'email'    => 'budi@smartkos.com',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $this->otherStaffUser = User::factory()->create([
            'name'     => 'Doni Pratama',
            'username' => 'donistaff',
            'email'    => 'doni@smartkos.com',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $this->locationA = Location::create([
            'name'        => 'Kos Sakura Indah',
            'address'     => 'Jl. Sakura No. 10',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->locationB = Location::create([
            'name'        => 'Kos Cempaka Asri',
            'address'     => 'Jl. Cempaka No. 5',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->roomA1 = Room::create([
            'location_id' => $this->locationA->id,
            'floor_name'  => 1,
            'room_number' => 101,
            'rent_amount' => 1500000,
            'status'      => 'active',
        ]);

        $this->roomB1 = Room::create([
            'location_id' => $this->locationB->id,
            'floor_name'  => 1,
            'room_number' => 201,
            'rent_amount' => 1800000,
            'status'      => 'active',
        ]);
    }

    public function test_staff_can_view_post_index_and_create_page(): void
    {
        // Index
        $responseIndex = $this->actingAs($this->staffUser)->get(route('staff.posts.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Daftar Pengumuman Diterbitkan');

        // Create page
        $responseCreate = $this->actingAs($this->staffUser)->get(route('staff.posts.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Form Pembuatan Pengumuman');
        $responseCreate->assertSee('Kos Sakura Indah');
        $responseCreate->assertSee('Kos Cempaka Asri');
    }

    public function test_staff_can_create_post_targeting_all_locations(): void
    {
        $response = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title'       => 'Pemberitahuan Hari Libur Nasional',
            'content'     => 'Seluruh layanan administrasi libur pada hari Minggu.',
            'target_type' => 'all',
        ]);

        $response->assertRedirect(route('staff.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'author_id' => $this->staffUser->id,
            'title'     => 'Pemberitahuan Hari Libur Nasional',
            'status'    => 'published',
        ]);

        $post = Post::where('title', 'Pemberitahuan Hari Libur Nasional')->first();
        $this->assertNotNull($post);
        $this->assertDatabaseHas('post_targets', [
            'post_id'     => $post->id,
            'target_type' => 'all',
            'location_id' => null,
            'room_id'     => null,
        ]);
    }

    public function test_staff_can_create_post_targeting_specific_location(): void
    {
        $response = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title'       => 'Pembersihan Area Parkir Kos Sakura',
            'content'     => 'Diharapkan kendaraan diparkir di sisi timur.',
            'target_type' => 'location',
            'location_id' => $this->locationA->id,
        ]);

        $response->assertRedirect(route('staff.dashboard'));
        $response->assertSessionHas('success');

        $post = Post::where('title', 'Pembersihan Area Parkir Kos Sakura')->first();
        $this->assertNotNull($post);
        $this->assertDatabaseHas('post_targets', [
            'post_id'     => $post->id,
            'target_type' => 'location',
            'location_id' => $this->locationA->id,
            'room_id'     => null,
        ]);
    }

    public function test_staff_can_create_post_targeting_specific_room(): void
    {
        $response = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title'       => 'Pemeriksaan Instalasi Listrik Kamar 101',
            'content'     => 'Petugas PLN akan mengecek MCB kamar 101 besok.',
            'target_type' => 'room',
            'location_id' => $this->locationA->id,
            'room_id'     => $this->roomA1->id,
        ]);

        $response->assertRedirect(route('staff.dashboard'));
        $response->assertSessionHas('success');

        $post = Post::where('title', 'Pemeriksaan Instalasi Listrik Kamar 101')->first();
        $this->assertNotNull($post);
        $this->assertDatabaseHas('post_targets', [
            'post_id'     => $post->id,
            'target_type' => 'room',
            'location_id' => $this->locationA->id,
            'room_id'     => $this->roomA1->id,
        ]);
    }

    public function test_staff_can_create_post_with_image_upload(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('pengumuman_air.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title'       => 'Jadwal Pengurasan Toren Air',
            'content'     => 'Air akan dimatikan sementara dari jam 09:00 - 12:00.',
            'target_type' => 'all',
            'image'       => $file,
        ]);

        $response->assertRedirect(route('staff.dashboard'));
        $response->assertSessionHas('success');

        $post = Post::where('title', 'Jadwal Pengurasan Toren Air')->first();
        $this->assertNotNull($post);
        $this->assertNotNull($post->image_path);

        Storage::disk('public')->assertExists($post->image_path);
    }

    public function test_post_creation_requires_valid_target_type_and_content(): void
    {
        $response = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title'       => '',
            'content'     => '',
            'target_type' => 'invalid_target',
        ]);

        $response->assertSessionHasErrors(['title', 'content', 'target_type']);
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_post_creation_requires_location_when_target_is_location_or_room(): void
    {
        $response = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title'       => 'Judul Pengumuman',
            'content'     => 'Konten pengumuman',
            'target_type' => 'location',
            'location_id' => null,
        ]);

        $response->assertSessionHasErrors(['location_id']);

        $responseRoom = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title'       => 'Judul Pengumuman',
            'content'     => 'Konten pengumuman',
            'target_type' => 'room',
            'location_id' => null,
            'room_id'     => $this->roomA1->id,
        ]);

        $responseRoom->assertSessionHasErrors(['location_id']);
    }

    public function test_post_creation_requires_room_when_target_is_room(): void
    {
        $response = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title'       => 'Judul Pengumuman',
            'content'     => 'Konten pengumuman',
            'target_type' => 'room',
            'location_id' => $this->locationA->id,
            'room_id'     => null,
        ]);

        $response->assertSessionHasErrors(['room_id']);
    }

    public function test_post_creation_rejects_room_not_belonging_to_selected_location(): void
    {
        // roomB1 belongs to locationB, but location_id is locationA
        $response = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title'       => 'Judul Pengumuman',
            'content'     => 'Konten pengumuman',
            'target_type' => 'room',
            'location_id' => $this->locationA->id,
            'room_id'     => $this->roomB1->id,
        ]);

        $response->assertSessionHasErrors(['room_id']);
        $this->assertDatabaseMissing('posts', ['title' => 'Judul Pengumuman']);
    }

    public function test_staff_can_delete_own_post_and_removes_attached_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('notice.png', 100, 'image/png');
        $imagePath = $file->store('posts', 'public');

        $post = Post::create([
            'author_id'    => $this->staffUser->id,
            'title'        => 'Pengumuman Sementara',
            'content'      => 'Konten yang akan dihapus.',
            'image_path'   => $imagePath,
            'status'       => 'published',
            'published_at' => now(),
        ]);
        PostTarget::create([
            'post_id'     => $post->id,
            'target_type' => 'all',
        ]);

        Storage::disk('public')->assertExists($imagePath);

        $response = $this->actingAs($this->staffUser)->delete(route('staff.posts.destroy', $post));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('post_targets', ['post_id' => $post->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_staff_cannot_delete_post_created_by_another_staff(): void
    {
        $post = Post::create([
            'author_id'    => $this->otherStaffUser->id,
            'title'        => 'Pengumuman Milik Doni',
            'content'      => 'Konten milik staff lain.',
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->staffUser)->delete(route('staff.posts.destroy', $post));

        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_guest_and_penyewa_cannot_access_post_creation(): void
    {
        $tenantUser = User::factory()->create([
            'role'   => 'penyewa',
            'status' => 'active',
        ]);

        // Guest
        $this->get(route('staff.posts.index'))->assertRedirect(route('login'));
        $this->get(route('staff.posts.create'))->assertRedirect(route('login'));
        $this->post(route('staff.posts.store'), [
            'title'       => 'Tamu Post',
            'content'     => 'Isi',
            'target_type' => 'all',
        ])->assertRedirect(route('login'));

        // Penyewa
        $this->actingAs($tenantUser)->get(route('staff.posts.index'))->assertStatus(403);
        $this->actingAs($tenantUser)->get(route('staff.posts.create'))->assertStatus(403);
        $this->actingAs($tenantUser)->post(route('staff.posts.store'), [
            'title'       => 'Penyewa Post',
            'content'     => 'Isi',
            'target_type' => 'all',
        ])->assertStatus(403);
    }
}
