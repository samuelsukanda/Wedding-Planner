<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyRangeTest extends TestCase
{
    use RefreshDatabase;

    private function userWithWedding(): User
    {
        $wedding = Wedding::create([
            'groom_name' => 'Groom',
            'bride_name' => 'Bride',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);

        return User::factory()->create(['wedding_id' => $wedding->id]);
    }

    private function vendorPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Vendor Uji',
            'category' => 'Catering',
            'contact' => '081234567890',
            'address' => 'Jakarta',
            'google_maps_url' => null,
            'package' => 'Paket A',
            'price' => '1500000',
            'rating' => 4.5,
            'review' => null,
            'booking_status' => 'Not Contacted',
        ], $overrides);
    }

    /**
     * Kolom uang bertipe decimal(15,2). Angka yang lebih panjang dulu lolos
     * validasi lalu meledak jadi QueryException SQLSTATE[22003].
     */
    public function test_vendor_price_beyond_column_capacity_is_rejected_with_validation_error(): void
    {
        $user = $this->userWithWedding();

        $response = $this->actingAs($user)->post('/vendors', $this->vendorPayload([
            'price' => '1000001111111111400000000',
        ]));

        $response->assertSessionHasErrors('price');
        $this->assertDatabaseCount('vendors', 0);
    }

    public function test_vendor_price_at_the_limit_is_accepted(): void
    {
        $user = $this->userWithWedding();

        $this->actingAs($user)->post('/vendors', $this->vendorPayload([
            'price' => '9999999999999',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('vendors', ['price' => 9999999999999]);
    }

    public function test_vendor_price_is_normalized_before_validating(): void
    {
        $user = $this->userWithWedding();

        // Format "<Rp> 1.500.000" harus diterima, bukan dianggap bukan angka.
        $this->actingAs($user)->post('/vendors', $this->vendorPayload([
            'price' => 'Rp 1.500.000',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('vendors', ['price' => 1500000]);
    }

    public function test_existing_vendor_price_cannot_be_updated_beyond_capacity(): void
    {
        $user = $this->userWithWedding();
        $vendor = Vendor::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Vendor Lama',
            'category' => 'Catering',
            'price' => 1500000,
        ]);

        $this->actingAs($user)->put("/vendors/{$vendor->id}", $this->vendorPayload([
            'price' => '1000001111111111400000000',
        ]))->assertSessionHasErrors('price');

        $this->assertSame(1500000.0, (float) $vendor->fresh()->price);
    }
}