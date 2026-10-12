<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestPaxTest extends TestCase
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

    private function guestPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Bapak Budi',
            'name' => 'Budi Santoso',
            'category' => 'Invitasi',
            'phone' => '081234567890',
            'address' => 'Jakarta',
            'attendance_status' => 'Attend',
            'guest_count' => 2,
        ], $overrides);
    }

    public function test_pax_at_the_three_digit_limit_is_accepted(): void
    {
        $user = $this->userWithWedding();

        $this->actingAs($user)->post('/guests', $this->guestPayload([
            'guest_count' => 999,
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('guests', ['guest_count' => 999]);
    }

    public function test_pax_beyond_three_digits_is_rejected_with_validation_error(): void
    {
        $user = $this->userWithWedding();

        $this->actingAs($user)->post('/guests', $this->guestPayload([
            'guest_count' => 1000,
        ]))->assertSessionHasErrors('guest_count');

        $this->assertDatabaseCount('guests', 0);
    }
}