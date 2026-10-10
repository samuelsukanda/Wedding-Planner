<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Souvenir;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SouvenirTest extends TestCase
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

    public function test_index_renders(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/souvenirs')
            ->assertOk()
            ->assertSee('Daftar Seserahan');
    }

    public function test_item_can_be_created(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/souvenirs', [
            'name' => 'Mug Keramik Custom',
            'planned_price' => 45000,
            'actual_cost' => 50000,
            'status' => 'Belum Dipilih',
        ])->assertRedirect(route('souvenirs.index'));

        $this->assertDatabaseHas('souvenirs', [
            'wedding_id' => $user->wedding_id,
            'name' => 'Mug Keramik Custom',
            'planned_price' => 45000,
        ]);
    }

    public function test_name_is_required(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/souvenirs', ['planned_price' => 1000])
            ->assertSessionHasErrors('name');
    }

    /**
     * Item berstatus Diterima harus otomatis jadi pengeluaran Budget Planner.
     */
    public function test_received_item_posts_to_budget_planner(): void
    {
        $user = $this->makeUser();

        $vendor = Vendor::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Souvenir Store',
            'category' => 'Souvenir',
        ]);

        $this->actingAs($user)->post('/souvenirs', [
            'name' => 'Mug Keramik',
            'vendor_id' => $vendor->id,
            'planned_price' => 45000,
            'actual_cost' => 50000,
            'status' => Souvenir::STATUS_RECEIVED,
        ])->assertRedirect(route('souvenirs.index'));

        $this->assertDatabaseHas('budgets', [
            'wedding_id' => $user->wedding_id,
            'category' => 'Seserahan',
            'item_name' => 'Mug Keramik',
            'actual_cost' => 50000,
            'vendor_id' => $vendor->id,
        ]);
    }

    /**
     * Item yang belum Diterima tidak boleh jadi pengeluaran.
     */
    public function test_pending_item_does_not_post_to_budget(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/souvenirs', [
            'name' => 'Kain Batik',
            'planned_price' => 200000,
            'actual_cost' => 200000,
            'status' => 'Sedang Dipilih',
        ]);

        $this->assertDatabaseCount('budgets', 0);
    }

    /**
     * Idempotensi: mengubah status berulang tidak boleh menggandakan baris
     * anggaran. Ini yang dijaga FK souvenirs_id di tabel budgets.
     */
    public function test_toggling_status_repeatedly_does_not_duplicate_budget_row(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/souvenirs', [
            'name' => 'Mug',
            'planned_price' => 45000,
            'actual_cost' => 50000,
            'status' => 'Belum Dipilih',
        ]);

        $souvenir = Souvenir::first();
        $this->assertDatabaseCount('budgets', 0);

        // Diterima -> masuk budget, dibalik -> keluar, lalu dua kali berturut-turut.
        foreach (['Diterima', 'Belum Dipilih', 'Diterima', 'Diterima'] as $status) {
            $this->actingAs($user)->put('/souvenirs/' . $souvenir->id, [
                'name' => 'Mug',
                'planned_price' => 45000,
                'actual_cost' => 50000,
                'status' => $status,
            ])->assertRedirect(route('souvenirs.index'));
        }

        $this->assertDatabaseCount('budgets', 1);
        $this->assertDatabaseCount('souvenirs', 1);
    }

    public function test_updating_price_refreshes_budget_row(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/souvenirs', [
            'name' => 'Mug',
            'planned_price' => 45000,
            'actual_cost' => 50000,
            'status' => Souvenir::STATUS_RECEIVED,
        ]);

        $souvenir = Souvenir::first();

        $this->actingAs($user)->put('/souvenirs/' . $souvenir->id, [
            'name' => 'Mug',
            'planned_price' => 45000,
            'actual_cost' => 47000,
            'status' => Souvenir::STATUS_RECEIVED,
        ]);

        $this->assertDatabaseCount('budgets', 1);
        $this->assertDatabaseHas('budgets', ['actual_cost' => 47000]);
    }

    public function test_deleting_item_removes_its_budget_row(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/souvenirs', [
            'name' => 'Mug',
            'planned_price' => 45000,
            'actual_cost' => 50000,
            'status' => Souvenir::STATUS_RECEIVED,
        ]);

        $souvenir = Souvenir::first();
        $this->assertDatabaseCount('budgets', 1);

        $this->actingAs($user)->delete('/souvenirs/' . $souvenir->id)
            ->assertRedirect(route('souvenirs.index'));

        $this->assertDatabaseCount('souvenirs', 0);
        $this->assertDatabaseCount('budgets', 0);
    }

    public function test_variance_is_computed(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/souvenirs', [
            'name' => 'Mug',
            'planned_price' => 50000,
            'actual_cost' => 45000,
            'status' => Souvenir::STATUS_RECEIVED,
        ]);

        $this->assertSame(5000.0, Souvenir::first()->variance());
    }

    public function test_other_wedding_data_is_invisible(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser('b@example.com');

        $otherItem = Souvenir::create([
            'wedding_id' => $other->wedding_id,
            'name' => 'SouvenirRahasia',
            'status' => Souvenir::STATUS_RECEIVED,
        ]);

        Souvenir::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Milik Saya',
            'status' => Souvenir::STATUS_RECEIVED,
        ]);

        $this->actingAs($user)->get('/souvenirs')
            ->assertOk()
            ->assertSee('Milik Saya')
            ->assertDontSee('SouvenirRahasia');

        $this->actingAs($user)->delete('/souvenirs/' . $otherItem->id)
            ->assertNotFound();

        // Baris budget milik pasangan lain juga tidak boleh ikut terhapus.
        $this->assertDatabaseHas('budgets', ['item_name' => 'SouvenirRahasia']);
    }

    public function test_budget_total_includes_received_souvenirs(): void
    {
        $user = $this->makeUser();

        $user->wedding->budgets()->create([
            'category' => 'Venue',
            'item_name' => 'Venue Resepsi',
            'planned_budget' => 40000000,
            'actual_cost' => 40000000,
        ]);

        $this->actingAs($user)->post('/souvenirs', [
            'name' => 'Mug',
            'planned_price' => 45000,
            'actual_cost' => 50000,
            'status' => Souvenir::STATUS_RECEIVED,
        ]);

        $total = (float) Budget::sum('actual_cost');
        $this->assertSame(40050000.0, $total);
    }
}