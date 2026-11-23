<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\ComplaintAction;
use App\Models\Location;
use App\Models\LocationPhoto;
use App\Models\ManualPayment;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\PostTarget;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Source images from database/seeders/images/ folder.
     * Stored once into storage/app/public/<dir>/ and reused throughout seeding.
     */
    private array $storedImages = [];

    public function run(): void
    {
        // --- 0. Prepare storage directories & copy source images ---
        $this->prepareImages();

        // --- 1. Admin Account (1 buah) ---
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Smart Kos',
                'email' => 'admin@smartkos.test',
                'phone' => '081200000000',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // --- 2. Staff Accounts (5 buah: staff_1 s/d staff_5) ---
        $staffUsers = [];
        for ($i = 1; $i <= 5; $i++) {
            $staffUsers[$i] = User::updateOrCreate(
                ['username' => "staff_{$i}"],
                [
                    'name' => "Staff {$i}",
                    'email' => "staff_{$i}@smartkos.test",
                    'phone' => "081200000{$i}10",
                    'password' => bcrypt('password123'),
                    'role' => 'staff',
                    'status' => 'active',
                ]
            );
        }

        // --- 3. Penyewa Accounts + TenantProfile (5 buah) ---
        $penyewaUsers = [];
        $penyewaProfiles = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = User::updateOrCreate(
                ['username' => "penyewa_{$i}"],
                [
                    'name' => "Penyewa {$i}",
                    'email' => "penyewa_{$i}@smartkos.test",
                    'phone' => "0812345678{$i}0",
                    'password' => bcrypt('password123'),
                    'role' => 'penyewa',
                    'status' => 'active',
                ]
            );

            $profile = TenantProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => "0812345678{$i}0",
                    'address' => "Jl. Penyewa No. {$i}, Jakarta",
                    'registered_at' => now()->subMonths(3),
                ]
            );

            $penyewaUsers[$i] = $user;
            $penyewaProfiles[$i] = $profile;
        }

        // --- 4. Locations + Photos + Rooms + Assignments ---
        // Scenario map (per kos):
        //   Kos 1  → penyewa_1, assignment ACTIVE, lunas (approved manual payment)
        //   Kos 2  → penyewa_2, assignment ACTIVE, menunggak (deadline lalu, belum bayar)
        //   Kos 3  → penyewa_3, assignment ACTIVE, perlu verifikasi (pending manual payment + bukti)
        //   Kos 4  → penyewa_4, assignment ACTIVE, ditolak (rejected manual payment)
        //   Kos 5  → penyewa_5, assignment ACTIVE, lunas bulan ini + kamar lain kosong/tidak ada assignment

        $assignments = [];

        for ($x = 1; $x <= 5; $x++) {
            $location = Location::updateOrCreate(
                ['name' => "Kos {$x}"],
                [
                    'address' => "Jl. Kos Nomor {$x}, Jakarta Selatan",
                    'google_maps_url' => 'https://maps.google.com/?q=-6.2,106.8',
                    'description' => "Kos {$x} merupakan hunian nyaman dengan 2 lantai, kamar bersih, air PAM, listrik token, dan wifi. Cocok untuk mahasiswa dan karyawan.",
                    'floor_count' => 2,
                    'status' => 'active',
                ]
            );

            // Location photo (foto kos)
            $this->attachLocationPhoto($location, 'location');

            // Room photos (foto kamar)
            $this->attachLocationPhoto($location, 'room');

            // === Lantai 1: Kamar 1–5 ===
            $room1 = null;
            for ($r = 1; $r <= 5; $r++) {
                // Kamar status:
                //   r=1 → ditempati (penyewa_x)
                //   r=2 → available
                //   r=3 → available
                //   r=4 → available (Kos 2 ini kamarnya = deadline scenario jadi room status tetap active)
                //   r=5 → available
                $room = Room::updateOrCreate(
                    [
                        'location_id' => $location->id,
                        'floor_name' => 1,
                        'room_number' => $r,
                    ],
                    [
                        'rent_amount' => 1_000_000,
                        'status' => 'active',
                    ]
                );
                if ($r === 1) {
                    $room1 = $room;
                }
            }

            // === Lantai 2: Kamar 6–10 ===
            for ($r = 6; $r <= 10; $r++) {
                Room::updateOrCreate(
                    [
                        'location_id' => $location->id,
                        'floor_name' => 2,
                        'room_number' => $r,
                    ],
                    [
                        'rent_amount' => 1_200_000,
                        'status' => 'active',
                    ]
                );
            }

            // === RoomAssignment untuk penyewa_x di Kamar 1 Lantai 1 ===
            if ($room1 && isset($penyewaProfiles[$x])) {
                // Scenario per kos: due date berbeda
                $scenarios = [
                    1 => ['start' => now()->subMonths(2)->startOfMonth(), 'billing_due_day' => now()->day],      // Kos 1: lunas bulan ini
                    2 => ['start' => now()->subMonths(3)->startOfMonth(), 'billing_due_day' => now()->subDays(22)->day], // Kos 2: sudah lewat 22 hari (menunggak)
                    3 => ['start' => now()->subMonths(1)->startOfMonth(), 'billing_due_day' => now()->subDays(5)->day],  // Kos 3: 5 hari lalu - ada pending
                    4 => ['start' => now()->subMonths(2)->startOfMonth(), 'billing_due_day' => now()->subDays(10)->day], // Kos 4: 10 hari lalu - rejected
                    5 => ['start' => now()->subMonths(1)->startOfMonth(), 'billing_due_day' => now()->addDays(7)->day],  // Kos 5: masih 7 hari lagi
                ];

                $sc = $scenarios[$x];
                $start = $sc['start'];

                $assignment = RoomAssignment::updateOrCreate(
                    [
                        'room_id' => $room1->id,
                        'tenant_id' => $penyewaProfiles[$x]->id,
                    ],
                    [
                        'start_date' => $start->toDateString(),
                        'end_date' => $start->copy()->addMonths(12)->endOfMonth()->toDateString(),
                        'status' => 'active',
                        'rent_amount_snapshot' => 1_000_000,
                        'payment_cycle' => 'monthly',
                        'billing_due_day' => $sc['billing_due_day'],
                        'next_due_date' => null,
                    ]
                );

                $assignments[$x] = $assignment;
            }
        }

        // --- 5. Seed Payments per scenario ---

        // === Kos 1 — LUNAS: approved manual payment ===
        if (isset($assignments[1])) {
            ManualPayment::updateOrCreate(
                ['room_assignment_id' => $assignments[1]->id, 'submitted_by' => $penyewaUsers[1]->id, 'status' => 'approved'],
                [
                    'amount' => 1_000_000,
                    'bank_name' => 'BCA',
                    'bank_account_number' => '1234567890',
                    'proof_path' => $this->storedImages['bukti_transfer'],
                    'status' => 'approved',
                    'verified_by' => $admin->id,
                    'submitted_at' => now()->subDays(5),
                    'verified_at' => now()->subDays(4),
                    'rejection_reason' => null,
                ]
            );
        }

        // === Kos 2 — MENUNGGAK: tidak ada pembayaran apapun ===
        // (tidak perlu insert, assignment sudah ada dengan billing_due_day yang sudah lewat)

        // === Kos 3 — PERLU VERIFIKASI: pending manual payment dengan bukti transfer ===
        if (isset($assignments[3])) {
            ManualPayment::updateOrCreate(
                ['room_assignment_id' => $assignments[3]->id, 'submitted_by' => $penyewaUsers[3]->id, 'status' => 'pending'],
                [
                    'amount' => 1_000_000,
                    'bank_name' => 'Mandiri',
                    'bank_account_number' => '0987654321',
                    'proof_path' => $this->storedImages['bukti_transfer'],
                    'status' => 'pending',
                    'verified_by' => null,
                    'submitted_at' => now()->subDays(2),
                    'verified_at' => null,
                    'rejection_reason' => null,
                ]
            );
        }

        // === Kos 4 — DITOLAK: rejected manual payment ===
        if (isset($assignments[4])) {
            ManualPayment::updateOrCreate(
                ['room_assignment_id' => $assignments[4]->id, 'submitted_by' => $penyewaUsers[4]->id, 'status' => 'rejected'],
                [
                    'amount' => 500_000,  // Jumlah salah (setengah sewa)
                    'bank_name' => 'BNI',
                    'bank_account_number' => '1122334455',
                    'proof_path' => $this->storedImages['bukti_transfer'],
                    'status' => 'rejected',
                    'verified_by' => $admin->id,
                    'submitted_at' => now()->subDays(8),
                    'verified_at' => now()->subDays(7),
                    'rejection_reason' => 'Jumlah transfer tidak sesuai. Harap transfer sesuai nominal sewa Rp 1.000.000. Silakan upload ulang bukti transfer yang benar.',
                ]
            );
        }

        // === Kos 5 — H-7 (belum jatuh tempo): tidak ada pembayaran ===
        // (tidak perlu insert, cukup assignment dengan billing_due_day 7 hari ke depan)

        // --- 6. Posts (Pengumuman) ---
        $this->seedPosts($admin, $staffUsers[1], $assignments);

        // --- 7. Complaints (Laporan Penyewa) ---
        $this->seedComplaints($penyewaProfiles, $staffUsers[1], $admin);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Copy source images from database/seeders/images/ into storage disk
     * and remember the resulting relative paths.
     */
    private function prepareImages(): void
    {
        $dirs = ['locations', 'posts', 'complaints', 'complaint-actions', 'payment-proofs'];
        foreach ($dirs as $dir) {
            Storage::disk('public')->makeDirectory($dir);
        }

        $src = database_path('seeders/images');

        $map = [
            'foto_kos' => 'foto kos.jpg',
            'kamar_kos' => 'kamar kos.jpg',
            'perbaikan_pipa' => 'perbaikan pipa air pam.jpg',
            'tagihan_listrik' => 'tagihan listrik.jpg',
            'bukti_transfer' => 'bukti transfer.jpg',
        ];

        foreach ($map as $key => $filename) {
            $sourcePath = $src.DIRECTORY_SEPARATOR.$filename;

            if (! File::exists($sourcePath)) {
                $this->command?->warn("Gambar tidak ditemukan: {$filename}");
                // Use a fallback empty path so seeder doesn't crash
                $this->storedImages[$key] = null;

                continue;
            }

            // Copy to each relevant directory where needed
            $destinations = match ($key) {
                'foto_kos' => ['locations/foto-kos.jpg'],
                'kamar_kos' => ['locations/kamar-kos.jpg'],
                'perbaikan_pipa' => ['posts/perbaikan-pipa.jpg'],
                'tagihan_listrik' => [
                    'complaints/tagihan-listrik.jpg',
                    'complaint-actions/tagihan-listrik.jpg',
                ],
                'bukti_transfer' => ['payment-proofs/bukti-transfer.jpg'],
                default => [],
            };

            foreach ($destinations as $dest) {
                $destPath = storage_path('app/public/'.$dest);
                if (! File::exists($destPath)) {
                    File::copy($sourcePath, $destPath);
                }
            }

            // Store the first destination as the canonical path for this key
            $this->storedImages[$key] = $destinations[0] ?? null;
        }
    }

    /**
     * Attach a location photo (type: 'location' or 'room') to a Location.
     * Reuses already-stored files; does NOT duplicate DB rows on re-seed.
     */
    private function attachLocationPhoto(Location $location, string $type): void
    {
        $path = $type === 'room'
            ? 'locations/kamar-kos.jpg'
            : 'locations/foto-kos.jpg';

        $exists = LocationPhoto::where('location_id', $location->id)
            ->where('type', $type)
            ->exists();

        if (! $exists) {
            LocationPhoto::create([
                'location_id' => $location->id,
                'path' => $path,
                'type' => $type,
            ]);
        }
    }

    /**
     * Seed announcements (Posts) with varied states.
     *
     * Scenarios:
     *   Post 1 – Aktif, berlaku semua, foto perbaikan pipa (by admin)
     *   Post 2 – Aktif, ditargetkan ke Kos 1, foto perbaikan pipa (by staff_1)
     *   Post 3 – Sudah kadaluarsa (expires_at kemarin), foto perbaikan pipa
     *   Post 4 – Tanpa gambar, berlaku semua
     */
    private function seedPosts(User $admin, User $staff1, array $assignments): void
    {
        $pipa = $this->storedImages['perbaikan_pipa'];

        // Post 1 — Aktif global, ada gambar perbaikan pipa (by admin)
        $post1 = Post::updateOrCreate(
            ['title' => '[PENGUMUMAN] Jadwal Perbaikan Pipa Air PAM'],
            [
                'author_id' => $admin->id,
                'content' => "Yth. Seluruh Penghuni Smart Kos,\n\nKami informasikan bahwa akan dilaksanakan perbaikan pipa air PAM pada semua lokasi kos pada tanggal 25–26 November 2026 mulai pukul 08.00–16.00 WIB.\n\nSelama proses perbaikan berlangsung, pasokan air akan dihentikan sementara. Harap menyediakan persediaan air sebelum perbaikan dimulai.\n\nMohon maaf atas ketidaknyamanan yang ditimbulkan.\n\nHormat kami,\nManajemen Smart Kos",
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'expires_at' => now()->addDays(5),
            ]
        );
        PostTarget::updateOrCreate(
            ['post_id' => $post1->id, 'target_type' => 'all'],
            ['location_id' => null, 'room_id' => null, 'created_at' => now()]
        );
        if ($pipa && ! $post1->images()->exists()) {
            PostImage::create(['post_id' => $post1->id, 'path' => $pipa, 'order' => 1]);
        }

        // Post 2 — Aktif, ditargetkan ke Kos 1 saja (by staff_1)
        $location1 = Location::where('name', 'Kos 1')->first();
        $post2 = Post::updateOrCreate(
            ['title' => '[KHUSUS KOS 1] Pengingat Kebersihan Lorong'],
            [
                'author_id' => $staff1->id,
                'content' => "Kepada penghuni Kos 1,\n\nMohon perhatiannya untuk menjaga kebersihan lorong dan area bersama. Jangan meletakkan barang di depan pintu kamar berlebihan.\n\nTerima kasih atas kerjasamanya.",
                'status' => 'published',
                'published_at' => now()->subDay(),
                'expires_at' => now()->addDays(14),
            ]
        );
        if ($location1) {
            PostTarget::updateOrCreate(
                ['post_id' => $post2->id, 'target_type' => 'location'],
                ['location_id' => $location1->id, 'room_id' => null, 'created_at' => now()]
            );
        }
        if ($pipa && ! $post2->images()->exists()) {
            PostImage::create(['post_id' => $post2->id, 'path' => $pipa, 'order' => 1]);
        }

        // Post 3 — Sudah kadaluarsa kemarin
        $post3 = Post::updateOrCreate(
            ['title' => '[SELESAI] Perbaikan Atap Lantai 2'],
            [
                'author_id' => $admin->id,
                'content' => 'Pemberitahuan perbaikan atap lantai 2 telah selesai dilaksanakan pada tanggal 15 November 2026. Terima kasih atas kesabaran seluruh penghuni.',
                'status' => 'published',
                'published_at' => now()->subDays(10),
                'expires_at' => now()->subDay(), // sudah expired
            ]
        );
        PostTarget::updateOrCreate(
            ['post_id' => $post3->id, 'target_type' => 'all'],
            ['location_id' => null, 'room_id' => null, 'created_at' => now()]
        );
        if ($pipa && ! $post3->images()->exists()) {
            PostImage::create(['post_id' => $post3->id, 'path' => $pipa, 'order' => 1]);
        }

        // Post 4 — Aktif, tanpa gambar, semua penghuni
        $post4 = Post::updateOrCreate(
            ['title' => '[INFO] Nomor Kontak Darurat Pengelola'],
            [
                'author_id' => $admin->id,
                'content' => "Untuk keperluan darurat, harap hubungi pengelola di nomor berikut:\n\n📞 Admin: 081200000000\n📞 Staff Piket: 081200000110\n\nLayanan darurat tersedia 24 jam.",
                'status' => 'published',
                'published_at' => now()->subDays(7),
                'expires_at' => null,
            ]
        );
        PostTarget::updateOrCreate(
            ['post_id' => $post4->id, 'target_type' => 'all'],
            ['location_id' => null, 'room_id' => null, 'created_at' => now()]
        );
    }

    /**
     * Seed complaints with varied states.
     *
     * Scenarios:
     *   Complaint 1 — open (belum ada tindakan), foto tagihan listrik (penyewa_1)
     *   Complaint 2 — in_progress (ada tindakan staff, belum selesai), foto tagihan listrik (penyewa_2)
     *   Complaint 3 — resolved (sudah selesai + tindakan), foto tagihan listrik (penyewa_3)
     *   Complaint 4 — open + highlighted, tanpa foto (penyewa_4)
     *   Complaint 5 — resolved, tanpa foto (penyewa_5)
     */
    private function seedComplaints(array $profiles, User $staff1, User $admin): void
    {
        $listrik = $this->storedImages['tagihan_listrik'];
        $actionListrik = 'complaint-actions/tagihan-listrik.jpg';

        // Complaint 1 — open, foto tagihan listrik (penyewa_1)
        Complaint::updateOrCreate(
            [
                'tenant_id' => $profiles[1]->id,
                'title' => 'Tagihan Listrik Tidak Wajar Bulan Ini',
            ],
            [
                'description' => 'Tagihan listrik kamar saya bulan ini jauh lebih tinggi dari biasanya. Bulan sebelumnya sekitar Rp 80.000, tapi bulan ini mencapai Rp 250.000. Mohon pengecekan meteran listrik kamar saya.',
                'image_path' => $listrik,
                'status' => 'open',
                'highlighted' => false,
                'submitted_at' => now()->subDays(3),
            ]
        );

        // Complaint 2 — in_progress: ada tindakan staff, belum resolved (penyewa_2)
        $complaint2 = Complaint::updateOrCreate(
            [
                'tenant_id' => $profiles[2]->id,
                'title' => 'Tagihan Listrik Dobel Ditagih',
            ],
            [
                'description' => 'Saya menerima dua tagihan listrik untuk bulan yang sama. Sepertinya ada kesalahan pencatatan dari pengelola. Mohon segera diperbaiki.',
                'image_path' => $listrik,
                'status' => 'in_progress',
                'highlighted' => false,
                'submitted_at' => now()->subDays(6),
            ]
        );
        ComplaintAction::updateOrCreate(
            ['complaint_id' => $complaint2->id, 'staff_id' => $staff1->id],
            [
                'action_description' => 'Sudah dicek ke petugas PLN dan ditemukan memang terjadi kesalahan input data. Sedang dalam proses koreksi dari pihak PLN. Estimasi selesai 2–3 hari kerja.',
                'image_path' => Storage::disk('public')->exists($actionListrik) ? $actionListrik : null,
                'status' => 'in_progress',
                'completed_at' => null,
            ]
        );

        // Complaint 3 — resolved: tindakan selesai, foto balasan (penyewa_3)
        $complaint3 = Complaint::updateOrCreate(
            [
                'tenant_id' => $profiles[3]->id,
                'title' => 'Tagihan Listrik Token Tidak Masuk',
            ],
            [
                'description' => 'Saya sudah beli token listrik Rp 100.000 tapi angka token tidak mau diinput ke meteran. Sudah coba berkali-kali tapi gagal terus.',
                'image_path' => $listrik,
                'status' => 'resolved',
                'highlighted' => false,
                'submitted_at' => now()->subDays(10),
            ]
        );
        ComplaintAction::updateOrCreate(
            ['complaint_id' => $complaint3->id, 'staff_id' => $staff1->id],
            [
                'action_description' => 'Setelah pengecekan langsung, ditemukan ada kerusakan pada keypad meteran listrik. Meteran sudah diganti dengan yang baru oleh teknisi PLN. Token berhasil diinput dan listrik berjalan normal kembali. Masalah telah selesai.',
                'image_path' => Storage::disk('public')->exists($actionListrik) ? $actionListrik : null,
                'status' => 'resolved',
                'completed_at' => now()->subDays(8),
            ]
        );

        // Complaint 4 — open + highlighted (penyewa_4)
        Complaint::updateOrCreate(
            [
                'tenant_id' => $profiles[4]->id,
                'title' => 'Air Tidak Mengalir Sejak 2 Hari',
            ],
            [
                'description' => 'Sejak 2 hari yang lalu air di kamar mandi lantai 1 tidak mengalir sama sekali. Sudah lapor ke petugas piket tapi belum ada tindaklanjut. Mohon segera diperbaiki karena sangat mengganggu aktivitas sehari-hari.',
                'image_path' => null,
                'status' => 'open',
                'highlighted' => true, // Ditandai urgent
                'submitted_at' => now()->subDays(2),
            ]
        );

        // Complaint 5 — resolved, tanpa foto (penyewa_5)
        $complaint5 = Complaint::updateOrCreate(
            [
                'tenant_id' => $profiles[5]->id,
                'title' => 'Kunci Kamar Rusak',
            ],
            [
                'description' => 'Kunci kamar saya sudah mulai sulit dibuka. Harus muter-muter dulu baru bisa terbuka. Khawatir kalau lama-lama tidak bisa dibuka sama sekali.',
                'image_path' => null,
                'status' => 'resolved',
                'highlighted' => false,
                'submitted_at' => now()->subDays(5),
            ]
        );
        ComplaintAction::updateOrCreate(
            ['complaint_id' => $complaint5->id, 'staff_id' => $staff1->id],
            [
                'action_description' => 'Kunci pintu kamar sudah diganti dengan yang baru. Kunci lama mengalami keausan karena sudah dipakai lama. Penyewa sudah diberikan kunci cadangan sebanyak 2 buah.',
                'image_path' => null,
                'status' => 'resolved',
                'completed_at' => now()->subDays(4),
            ]
        );
    }
}
