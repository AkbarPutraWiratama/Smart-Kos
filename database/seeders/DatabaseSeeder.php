<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin Account (Hanya 1 buah, nama: admin)
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'admin',
                'email'    => 'admin@smartkos.test',
                'password' => bcrypt('password123'),
                'role'     => 'admin',
                'status'   => 'active',
            ]
        );

        // 2. Staff Accounts (5 akun: staff_1 s/d staff_5)
        for ($i = 1; $i <= 5; $i++) {
            User::updateOrCreate(
                ['username' => "staff_{$i}"],
                [
                    'name'     => "staff_{$i}",
                    'email'    => "staff_{$i}@smartkos.test",
                    'password' => bcrypt('password123'),
                    'role'     => 'staff',
                    'status'   => 'active',
                ]
            );
        }

        // 3. Penyewa Accounts (5 akun: penyewa_1 s/d penyewa_5) & Tenant Profiles
        $penyewaProfiles = [];
        for ($i = 1; $i <= 5; $i++) {
            $penyewaUser = User::updateOrCreate(
                ['username' => "penyewa_{$i}"],
                [
                    'name'     => "penyewa_{$i}",
                    'email'    => "penyewa_{$i}@smartkos.test",
                    'password' => bcrypt('password123'),
                    'role'     => 'penyewa',
                    'status'   => 'active',
                ]
            );

            $profile = TenantProfile::updateOrCreate(
                ['user_id' => $penyewaUser->id],
                [
                    'phone'         => "08123456789{$i}",
                    'address'       => "Alamat Penyewa {$i}",
                    'registered_at' => now()->subMonths(2),
                ]
            );

            $penyewaProfiles[$i] = $profile;
        }

        // 4. 5 Lokasi Kos Dummy (Kos 1 s/d Kos 5)
        // - Tiap lokasi memiliki 2 lantai
        // - 5 kamar di bawah (Lantai 1): Kamar 1 s/d 5
        // - 5 kamar di atas (Lantai 2): Kamar 6 s/d 10
        // - Setiap kamar di-set harga sewa Rp 1.000.000 per bulan
        // - Setiap penyewa_x di-assign di kamar 1 lantai 1 (bawah) pada kos x
        for ($x = 1; $x <= 5; $x++) {
            $location = Location::updateOrCreate(
                ['name' => "Kos {$x}"],
                [
                    'address'         => "Jl. Kos Dummy No. {$x}",
                    'google_maps_url' => 'https://maps.google.com',
                    'description'     => "Kos {$x} memiliki 2 lantai dengan fasilitas lengkap, bersih, dan nyaman.",
                    'floor_count'     => 2,
                    'status'          => 'active',
                ]
            );

            $room1Lantai1 = null;

            // Lantai 1 (Kamar 5 di bawah: kamar 1 s/d 5)
            for ($r = 1; $r <= 5; $r++) {
                $room = Room::updateOrCreate(
                    [
                        'location_id' => $location->id,
                        'floor_name'  => 1,
                        'room_number' => $r,
                    ],
                    [
                        'rent_amount' => 1000000,
                        'status'      => 'active',
                    ]
                );

                if ($r === 1) {
                    $room1Lantai1 = $room;
                }
            }

            // Lantai 2 (Kamar 5 di atas: kamar 6 s/d 10)
            for ($r = 6; $r <= 10; $r++) {
                Room::updateOrCreate(
                    [
                        'location_id' => $location->id,
                        'floor_name'  => 2,
                        'room_number' => $r,
                    ],
                    [
                        'rent_amount' => 1000000,
                        'status'      => 'active',
                    ]
                );
            }

            // Assign penyewa_x ke kamar 1 di lantai 1 (bawah) pada kos x
            if ($room1Lantai1 && isset($penyewaProfiles[$x])) {
                RoomAssignment::updateOrCreate(
                    [
                        'room_id'   => $room1Lantai1->id,
                        'tenant_id' => $penyewaProfiles[$x]->id,
                    ],
                    [
                        'start_date'           => now()->subMonths(1)->startOfMonth()->toDateString(),
                        'end_date'             => now()->addMonths(5)->endOfMonth()->toDateString(),
                        'status'               => 'active',
                        'rent_amount_snapshot' => 1000000,
                    ]
                );
            }
        }
    }
}
