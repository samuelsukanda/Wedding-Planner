<?php

namespace Tests\Feature;

use App\Models\ProposalBudget;
use App\Models\ProposalEvent;
use App\Models\ProposalChecklist;
use App\Models\ProposalGuest;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProposalTest extends TestCase
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

    public function test_index_renders_and_creates_event_once_per_wedding(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/proposals')
            ->assertOk()
            ->assertSee('Lamaran');

        $this->actingAs($user)->get('/proposals')->assertOk();

        // Satu acara per wedding: dua kali buka tidak menghasilkan dua baris.
        $this->assertDatabaseCount('proposal_events', 1);
    }

    public function test_event_info_can_be_saved(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->put('/proposals/event', [
            'event_date' => '2026-12-20',
            'event_time' => '10:00',
            'location' => 'Rumah Orang Tua',
            'theme' => 'Tradisi Jawa',
            'notes' => 'Perlu sound system',
        ])->assertRedirect(route('proposals.index'));

        $this->assertDatabaseHas('proposal_events', [
            'wedding_id' => $user->wedding_id,
            'event_time' => '10:00',
            'theme' => 'Tradisi Jawa',
        ]);

        $this->assertSame('2026-12-20', ProposalEvent::first()->event_date->toDateString());
    }

    public function test_checklist_item_can_be_added_updated_and_deleted(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/proposals/checklists', [
            'title' => 'Booking Cincin',
            'category' => 'Perhiasan',
            'deadline' => '2026-11-01',
            'priority' => 'High',
            'status' => 'Todo',
        ])->assertRedirect(route('proposals.index'));

        $item = ProposalChecklist::first();
        $this->assertSame('Booking Cincin', $item->title);

        $this->actingAs($user)->put('/proposals/checklists/' . $item->id, [
            'title' => 'Booking Cincin Custom',
            'status' => 'Done',
        ])->assertRedirect(route('proposals.index'));

        $this->assertDatabaseHas('proposal_checklists', [
            'id' => $item->id,
            'title' => 'Booking Cincin Custom',
            'status' => 'Done',
        ]);

        $this->actingAs($user)->delete('/proposals/checklists/' . $item->id)
            ->assertRedirect(route('proposals.index'));

        $this->assertDatabaseCount('proposal_checklists', 0);
    }

    public function test_overdue_checklist_is_reported_as_terlambat(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/proposals/checklists', [
            'title' => 'Sewa Baju',
            'deadline' => now()->subDay()->toDateString(),
            'status' => 'Todo',
        ]);

        $item = ProposalChecklist::first();
        $this->assertTrue($item->isOverdue());
        $this->assertSame('Terlambat', $item->statusLabel());

        // Item yang sudah Done tidak dianggap terlambat meski lewat tanggal.
        $item->update(['status' => 'Done']);
        $this->assertFalse($item->fresh()->isOverdue());
    }

    public function test_guest_can_be_added_with_default_count(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/proposals/guests', [
            'name' => 'Bpk. Budi',
            'title' => 'Bpk.',
            'relation' => 'Ayah',
            'guest_count' => 3,
        ])->assertRedirect(route('proposals.index'));

        $guest = ProposalGuest::first();
        $this->assertSame(3, $guest->guest_count);
        $this->assertSame('Pending', $guest->attendance_status);
    }

    public function test_budget_row_is_separate_from_wedding_budget(): void
    {
        $user = $this->makeUser();

        // Resepsi: 40jt. Lamaran: 15jt.
        $user->wedding->budgets()->create([
            'category' => 'Venue',
            'item_name' => 'Venue Resepsi',
            'planned_budget' => 40000000,
        ]);

        $this->actingAs($user)->post('/proposals/budgets', [
            'category' => 'Venue',
            'item_name' => 'Venue Lamaran',
            'planned_budget' => 15000000,
            'actual_cost' => 18000000,
        ])->assertRedirect(route('proposals.index'));

        // Business Rule 4: terpisah di tabelnya sendiri.
        $this->assertDatabaseCount('budgets', 1);
        $this->assertDatabaseCount('proposal_budgets', 1);

        $budget = ProposalBudget::first();
        $this->assertTrue($budget->isOverBudget());
        $this->assertSame(-3000000.0, $budget->variance());

        // ...tapi tetap ikut terhitung di total biaya persiapan (40 + 15).
        $this->actingAs($user)->get('/proposals')->assertSee('55.000.000');
    }

    public function test_vendor_can_be_toggled_in_and_out_of_lamaran(): void
    {
        $user = $this->makeUser();

        $vendor = Vendor::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Dekorasi Jaya',
            'category' => 'Dekorasi',
        ]);

        $this->assertNull($vendor->proposal_event_id);

        $this->actingAs($user)->post('/proposals/vendors/' . $vendor->id . '/toggle')
            ->assertRedirect(route('proposals.index'));

        $this->assertNotNull($vendor->fresh()->proposal_event_id);

        // Toggle lagi melepaskannya kembali ke daftar umum.
        $this->actingAs($user)->post('/proposals/vendors/' . $vendor->id . '/toggle')
            ->assertRedirect(route('proposals.index'));

        $this->assertNull($vendor->fresh()->proposal_event_id);
    }

    public function test_other_wedding_data_is_invisible(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser('b@example.com');

        $otherItem = ProposalChecklist::create([
            'wedding_id' => $other->wedding_id,
            'title' => 'Rahasia Party',
            'status' => 'Todo',
        ]);

        $own = ProposalChecklist::create([
            'wedding_id' => $user->wedding_id,
            'title' => 'Milik Saya',
            'status' => 'Todo',
        ]);

        $this->actingAs($user)->get('/proposals')
            ->assertOk()
            ->assertSee('Milik Saya')
            ->assertDontSee('Rahasia Party');

        // Route model binding 404 untuk data pasangan lain.
        $this->actingAs($user)->delete('/proposals/checklists/' . $otherItem->id)
            ->assertNotFound();

        $this->actingAs($user)->put('/proposals/checklists/' . $otherItem->id, [
            'title' => 'Dibajak',
        ])->assertNotFound();

        $this->assertDatabaseHas('proposal_checklists', ['id' => $own->id, 'title' => 'Milik Saya']);
    }
}