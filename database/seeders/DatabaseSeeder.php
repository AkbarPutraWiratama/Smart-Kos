<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin Account
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Owner Smart Kos',
                'email' => 'owner@smartkos.test',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // 2. Staff Account
        $staff = User::firstOrCreate(
            ['username' => 'staff'],
            [
                'name' => 'Budi Santoso (Staff)',
                'email' => 'staff@smartkos.test',
                'password' => bcrypt('password123'),
                'role' => 'staff',
                'status' => 'active',
            ]
        );

        // 3. Penyewa Account & Profile
        $penyewa = User::firstOrCreate(
            ['username' => 'penyewa'],
            [
                'name' => 'Ahmad Fauzi (Penyewa)',
                'email' => 'penyewa@smartkos.test',
                'password' => bcrypt('password123'),
                'role' => 'penyewa',
                'status' => 'active',
            ]
        );

        $tenantProfile = \App\Models\TenantProfile::firstOrCreate(
            ['user_id' => $penyewa->id],
            [
                'phone' => '081234567890',
                'address' => 'Jl. Asal No. 12',
                'registered_at' => now()->subMonths(2),
            ]
        );

        // 4. Demo Locations & Rooms
        $location = \App\Models\Location::firstOrCreate(
            ['name' => 'Kos Mawar Residence'],
            [
                'address' => 'Jl. Mawar Raya No. 45, Jakarta Selatan',
                'google_maps_url' => 'https://maps.google.com',
                'description' => 'Kos eksklusif dekat stasiun dan pusat perbelanjaan, fasilitas lengkap & WiFi cepat.',
                'floor_count' => 2,
                'status' => 'active',
            ]
        );

        // Lantai 1 Rooms
        for ($i = 101; $i <= 104; $i++) {
            $room = \App\Models\Room::firstOrCreate(
                [
                    'location_id' => $location->id,
                    'floor_name' => 'Lantai 1',
                    'room_number' => (string) $i,
                ],
                [
                    'rent_amount' => 1500000,
                    'status' => 'active',
                ]
            );

            // Assign room 101 to Ahmad Fauzi as occupied
            if ($i === 101) {
                \App\Models\RoomAssignment::firstOrCreate(
                    [
                        'room_id' => $room->id,
                        'tenant_id' => $tenantProfile->id,
                    ],
                    [
                        'start_date' => now()->subMonths(1)->startOfMonth(),
                        'status' => 'active',
                        'rent_amount_snapshot' => 1500000,
                    ]
                );
            }
        }

        // Lantai 2 Rooms
        for ($i = 201; $i <= 204; $i++) {
            \App\Models\Room::firstOrCreate(
                [
                    'location_id' => $location->id,
                    'floor_name' => 'Lantai 2',
                    'room_number' => (string) $i,
                ],
                [
                    'rent_amount' => 1600000,
                    'status' => 'active',
                ]
            );
        }

        // 5. Second Demo Location: Kos Melati Indah
        $location2 = \App\Models\Location::firstOrCreate(
            ['name' => 'Kos Melati Indah'],
            [
                'address' => 'Jl. Melati Putih No. 18, Sleman, Yogyakarta',
                'google_maps_url' => 'https://maps.google.com',
                'description' => 'Kos nyaman dan tenang untuk mahasiswa dan karyawan, dekat kampus ternama.',
                'floor_count' => 2,
                'status' => 'active',
            ]
        );

        // Location 2 - Lantai 1 Rooms
        for ($i = 101; $i <= 103; $i++) {
            \App\Models\Room::firstOrCreate(
                [
                    'location_id' => $location2->id,
                    'floor_name' => 'Lantai 1',
                    'room_number' => (string) $i,
                ],
                [
                    'rent_amount' => 1200000,
                    'status' => 'active',
                ]
            );
        }

        // Location 2 - Lantai 2 Rooms
        for ($i = 201; $i <= 203; $i++) {
            \App\Models\Room::firstOrCreate(
                [
                    'location_id' => $location2->id,
                    'floor_name' => 'Lantai 2',
                    'room_number' => (string) $i,
                ],
                [
                    'rent_amount' => 1300000,
                    'status' => 'active',
                ]
            );
        }
    }
}

