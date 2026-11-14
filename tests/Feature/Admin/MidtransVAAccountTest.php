<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\MidtransVAAccount;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransVAAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Location $location;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'   => 'admin',
            'status' => 'active',
        ]);

        $this->staff = User::factory()->create([
            'role'   => 'staff',
            'status' => 'active',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Sakura Indah',
            'address'     => 'Jl. Sakura No. 8',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->room = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 1,
            'room_number' => 101,
            'rent_amount' => 1200000,
            'status'      => 'active',
        ]);
    }

    public function test_midtrans_va_account_model_and_relations(): void
    {
        $va = MidtransVAAccount::create([
            'room_id'     => $this->room->id,
            'bank_code'   => 'bca',
            'va_number'   => '12345678901234',
            'external_id' => 'SMARTKOS-VA-101-1',
            'amount'      => 1200000,
            'status'      => 'active',
            'expires_at'  => now()->addHours(24),
        ]);

        $this->assertTrue($va->room->is($this->room));
        $this->assertTrue($this->room->midtransVaAccount->is($va));
        $this->assertTrue($va->isActive());
        $this->assertFalse($va->isExpired());
        $this->assertEquals('Rp 1.200.000', $va->formattedAmount());
        $this->assertEquals('BCA', $va->formattedBank());
    }

    public function test_expired_va_account_is_detected(): void
    {
        $va = MidtransVAAccount::create([
            'room_id'     => $this->room->id,
            'bank_code'   => 'bni',
            'va_number'   => '987654321',
            'external_id' => 'SMARTKOS-VA-101-2',
            'amount'      => 1200000,
            'status'      => 'active',
            'expires_at'  => now()->subHour(),
        ]);

        $this->assertFalse($va->isActive());
        $this->assertTrue($va->isExpired());
    }

    public function test_updating_room_rent_synchronizes_va_account_amount(): void
    {
        MidtransVAAccount::create([
            'room_id'     => $this->room->id,
            'bank_code'   => 'bca',
            'va_number'   => '12345678901234',
            'external_id' => 'SMARTKOS-VA-101-3',
            'amount'      => 1200000,
            'status'      => 'active',
            'expires_at'  => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.locations.rooms.update', [$this->location, $this->room]), [
            'floor_name'  => 1,
            'room_number' => 101,
            'rent_amount' => 1350000,
            'status'      => 'active',
            'password'    => 'password',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rooms', [
            'id'          => $this->room->id,
            'rent_amount' => 1350000,
        ]);

        $this->assertDatabaseHas('midtrans_va_accounts', [
            'room_id' => $this->room->id,
            'amount'  => 1350000,
        ]);
    }

    public function test_admin_can_sync_va_for_room(): void
    {
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code'        => '201',
                'status_message'     => 'Success, Bank Transfer transaction is created',
                'transaction_id'     => 'trx-admin-sync-1',
                'order_id'           => 'SMARTKOS-VA-' . $this->room->id . '-sync',
                'gross_amount'       => '1200000.00',
                'payment_type'       => 'bank_transfer',
                'transaction_time'   => now()->toDateTimeString(),
                'transaction_status' => 'pending',
                'va_numbers'         => [
                    [
                        'bank'      => 'bri',
                        'va_number' => '888877776666',
                    ],
                ],
                'expiry_time'        => now()->addHours(24)->toDateTimeString(),
            ], 201),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.locations.rooms.sync-va', [$this->location, $this->room]), [
            'bank_code' => 'bri',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('midtrans_va_accounts', [
            'room_id'   => $this->room->id,
            'bank_code' => 'bri',
            'va_number' => '888877776666',
            'status'    => 'active',
        ]);
    }

    public function test_non_admin_cannot_sync_va(): void
    {
        $response = $this->actingAs($this->staff)->post(route('admin.locations.rooms.sync-va', [$this->location, $this->room]), [
            'bank_code' => 'bca',
        ]);

        $response->assertStatus(403);
    }
}

