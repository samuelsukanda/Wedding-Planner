<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingsTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email = 'a@example.com'): User
    {
        $wedding = Wedding::create([
            'groom_name' => 'Groom',
            'bride_name' => 'Bride',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);

        return User::factory()->create([
            'name' => 'Samuel',
            'email' => $email,
            'wedding_id' => $wedding->id,
        ]);
    }

    private function makeGoal(User $user, array $attrs = []): SavingsGoal
    {
        // current_balance sengaja tidak diisi: model men-set-nya = initial_balance.
        return SavingsGoal::create(array_merge([
            'wedding_id' => $user->wedding_id,
            'name' => 'Dana Venue',
            'target_amount' => 100000000,
            'initial_balance' => 0,
            'periodic_amount' => 2000000,
            'frequency' => 'Bulanan',
            'status' => 'Aktif',
        ], $attrs));
    }

    public function test_goal_starts_with_initial_balance_as_current_balance(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/savings', [
            'name' => 'Dana Venue',
            'target_amount' => 100000000,
            'initial_balance' => 25000000,
        ])->assertRedirect(route('savings.index'));

        $goal = SavingsGoal::first();

        $this->assertSame('25000000.00', $goal->current_balance);
        $this->assertSame('25000000.00', $goal->initial_balance);
    }

    public function test_setoran_increases_balance(): void
    {
        $user = $this->makeUser();
        $goal = $this->makeGoal($user, ['initial_balance' => 10000000]);

        $this->actingAs($user)->post("/savings/{$goal->id}/transactions", [
            'type' => 'setoran',
            'amount' => 5000000,
            'transaction_date' => '2026-10-10',
        ])->assertSessionHasNoErrors();

        $goal->refresh();

        $this->assertSame('15000000.00', $goal->current_balance);
        // saldo 10.000.000 + 5.000.000 = 15.000.000 dari target 100.000.000 = 15%
        $this->assertSame(15.0, $goal->progressPercent());
        $this->assertSame(85000000.0, $goal->shortfall());
    }

    public function test_penarikan_more_than_balance_is_rejected(): void
    {
        $user = $this->makeUser();
        $goal = $this->makeGoal($user, ['initial_balance' => 10000000]);

        $this->actingAs($user)->post("/savings/{$goal->id}/transactions", [
            'type' => 'penarikan',
            'amount' => 10000001,
            'transaction_date' => '2026-10-10',
        ])->assertSessionHasErrors('amount');

        $this->assertSame(0, $goal->transactions()->count());
        $this->assertSame('10000000.00', $goal->fresh()->current_balance);
    }

    public function test_penarikan_within_balance_is_allowed_and_reduces_balance(): void
    {
        $user = $this->makeUser();
        $goal = $this->makeGoal($user, ['initial_balance' => 10000000]);

        $this->actingAs($user)->post("/savings/{$goal->id}/transactions", [
            'type' => 'penarikan',
            'amount' => 4000000,
            'transaction_date' => '2026-10-10',
        ])->assertSessionHasNoErrors();

        $this->assertSame('6000000.00', $goal->fresh()->current_balance);
    }

    public function test_one_wedding_can_have_multiple_goals(): void
    {
        $user = $this->makeUser();

        $this->makeGoal($user, ['name' => 'Dana Venue']);
        $this->makeGoal($user, ['name' => 'Dana Catering']);
        $this->makeGoal($user, ['name' => 'Dana Busana']);

        // PRD Business Rule 1: satu atau lebih target tabungan per wedding.
        $this->assertSame(3, SavingsGoal::count());
        $this->assertSame(3, SavingsGoal::where('wedding_id', $user->wedding_id)->count());
    }

    public function test_user_cannot_touch_goal_from_another_wedding(): void
    {
        $userA = $this->makeUser('a@example.com');
        $userB = $this->makeUser('b@example.com');

        $goalB = $this->makeGoal($userB, ['name' => 'Milik B']);

        $this->actingAs($userA)
            ->put("/savings/{$goalB->id}", ['name' => 'Dibajak', 'target_amount' => 1])
            ->assertNotFound();

        $this->actingAs($userA)
            ->delete("/savings/{$goalB->id}")
            ->assertNotFound();

        $this->assertSame('Milik B', $goalB->fresh()->name);

        // Goal B tetap ada; A tidak melihatnya karena global scope tenant.
        $this->assertSame(1, SavingsGoal::withoutGlobalScopes()->count());
        $this->assertSame(0, SavingsGoal::count());
    }

    public function test_status_becomes_tercapai_when_target_reached(): void
    {
        $user = $this->makeUser();
        $goal = $this->makeGoal($user, [
            'target_amount' => 10000000,
            'initial_balance' => 4000000,
        ]);

        $this->assertSame('Aktif', $goal->fresh()->status);

        $this->actingAs($user)->post("/savings/{$goal->id}/transactions", [
            'type' => 'setoran',
            'amount' => 6000000,
            'transaction_date' => '2026-10-10',
        ]);

        $goal->refresh();

        $this->assertSame('Tercapai', $goal->status);
        $this->assertSame(100.0, $goal->progressPercent());
        $this->assertSame(0.0, $goal->shortfall());
    }

    public function test_balance_always_matches_formula_after_delete(): void
    {
        $user = $this->makeUser();
        $goal = $this->makeGoal($user, ['initial_balance' => 5000000]);

        $this->actingAs($user)->post("/savings/{$goal->id}/transactions", [
            'type' => 'setoran', 'amount' => 3000000, 'transaction_date' => '2026-10-01',
        ]);
        $this->actingAs($user)->post("/savings/{$goal->id}/transactions", [
            'type' => 'penarikan', 'amount' => 1000000, 'transaction_date' => '2026-10-02',
        ]);

        $this->assertSame('7000000.00', $goal->fresh()->current_balance);

        $transaction = $goal->transactions()->latest('id')->first();

        $this->actingAs($user)
            ->delete("/savings/{$goal->id}/transactions/{$transaction->id}")
            ->assertSessionHasNoErrors();

        $this->assertSame('8000000.00', $goal->fresh()->current_balance);
    }

    public function test_saving_transaction_creates_reminder_using_existing_notifications_table(): void
    {
        $user = $this->makeUser();
        $goal = $this->makeGoal($user, ['initial_balance' => 0, 'frequency' => 'Bulanan']);

        $this->actingAs($user)->post("/savings/{$goal->id}/transactions", [
            'type' => 'setoran',
            'amount' => 2000000,
            'transaction_date' => '2026-10-10',
        ]);

        // PRD fitur "Pengingat setoran" + Business Rule 6.
        $reminder = Notification::where('type', 'savings_due')->first();

        $this->assertNotNull($reminder);
        $this->assertSame('Setoran tabungan: Dana Venue', $reminder->title);
        $this->assertSame('2026-11-10', $reminder->reminder_date->toDateString());
    }

    public function test_next_due_date_follows_frequency(): void
    {
        $user = $this->makeUser();

        $weekly = $this->makeGoal($user, ['name' => 'Mingguan', 'frequency' => 'Mingguan']);

        // Belum ada setoran -> jatuh tempo dihitung dari hari ini + frekuensi.
        $this->assertSame(
            \Carbon\Carbon::today()->addWeek()->toDateString(),
            $weekly->nextDueDate()
        );

        $this->actingAs($user)->post("/savings/{$weekly->id}/transactions", [
            'type' => 'setoran', 'amount' => 100000, 'transaction_date' => '2026-10-01',
        ]);
        $this->assertSame('2026-10-08', $weekly->fresh()->nextDueDate());

        $yearly = $this->makeGoal($user, ['name' => 'Tahunan', 'frequency' => 'Tahunan']);
        $this->actingAs($user)->post("/savings/{$yearly->id}/transactions", [
            'type' => 'setoran', 'amount' => 100000, 'transaction_date' => '2026-01-15',
        ]);
        $this->assertSame('2027-01-15', $yearly->fresh()->nextDueDate());
    }

    public function test_initial_balance_is_locked_once_transaction_exists(): void
    {
        $user = $this->makeUser();
        $goal = $this->makeGoal($user, ['initial_balance' => 10000000]);

        $this->actingAs($user)->post("/savings/{$goal->id}/transactions", [
            'type' => 'setoran', 'amount' => 1000000, 'transaction_date' => '2026-10-10',
        ]);

        $this->actingAs($user)->put("/savings/{$goal->id}", [
            'name' => 'Dana Venue',
            'target_amount' => 50000000,
            'initial_balance' => 99000000, // mencoba menabrak saldo awal
        ])->assertSessionHasNoErrors();

        $goal->refresh();

        $this->assertSame('10000000.00', $goal->initial_balance);
        $this->assertSame('11000000.00', $goal->current_balance);
    }

    public function test_savings_page_renders(): void
    {
        $user = $this->makeUser();
        $this->makeGoal($user);

        $this->actingAs($user)->get('/savings')
            ->assertOk()
            ->assertSee('Tabungan Pernikahan')
            ->assertSee('Dana Venue')
            ->assertSee('Grafik Perkembangan Tabungan');
    }
}