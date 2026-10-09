<?php

namespace Tests\Feature;

use App\Models\Gift;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftTest extends TestCase
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

    public function test_gift_can_be_created_with_only_giver_name(): void
    {
        $user = $this->userWithWedding();

        $this->actingAs($user)->post('/gifts', [
            'giver_name' => 'Bapak Budi',
        ])->assertRedirect(route('gifts.index'));

        // nominal & gift_type kolomnya NOT NULL, jadi harus ternormalisasi.
        $this->assertDatabaseHas('gifts', [
            'giver_name' => 'Bapak Budi',
            'gift_type' => 'Cash',
            'is_thank_you_sent' => false,
        ]);
    }

    public function test_gift_can_be_updated(): void
    {
        $user = $this->userWithWedding();
        $gift = Gift::create([
            'wedding_id' => $user->wedding_id,
            'giver_name' => 'Old Name',
            'gift_type' => 'Cash',
            'nominal' => 100000,
            'is_thank_you_sent' => false,
        ]);

        $this->actingAs($user)->put("/gifts/{$gift->id}", [
            'giver_name' => 'New Name',
            'gift_type' => 'Barang',
            'nominal' => 250000,
            'description' => 'Kue',
            'is_thank_you_sent' => '1',
        ])->assertRedirect(route('gifts.index'));

        $this->assertDatabaseHas('gifts', [
            'id' => $gift->id,
            'giver_name' => 'New Name',
            'gift_type' => 'Barang',
            'is_thank_you_sent' => true,
        ]);
    }

    public function test_user_cannot_update_gift_from_another_wedding(): void
    {
        $user = $this->userWithWedding();

        $otherWedding = Wedding::create([
            'groom_name' => 'Other',
            'bride_name' => 'Other',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);

        $foreign = Gift::create([
            'wedding_id' => $otherWedding->id,
            'giver_name' => 'Milik Orang Lain',
            'gift_type' => 'Cash',
            'nominal' => 1000,
            'is_thank_you_sent' => false,
        ]);

        $this->actingAs($user)->put("/gifts/{$foreign->id}", [
            'giver_name' => 'Dibajak',
        ])->assertNotFound();

        $this->assertDatabaseHas('gifts', [
            'id' => $foreign->id,
            'giver_name' => 'Milik Orang Lain',
        ]);
    }
}