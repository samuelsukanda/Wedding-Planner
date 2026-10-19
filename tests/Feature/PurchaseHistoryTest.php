<?php

namespace Tests\Feature;

use App\Models\UpgradeRequest;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseHistoryTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Wedding, 1: User} */
    private function couple(string $plan = 'pro'): array
    {
        $wedding = Wedding::create([
            'bride_name' => 'Siti',
            'groom_name' => 'Budi',
            'wedding_date' => '2026-12-12',
            'total_budget' => 100000000,
            'plan' => $plan,
        ]);

        $user = User::factory()->create([
            'email' => "pasangan+{$wedding->id}@example.com",
            'wedding_id' => $wedding->id,
        ]);

        return [$wedding, $user];
    }

    private function request(Wedding $wedding, string $status, int $amount): UpgradeRequest
    {
        return UpgradeRequest::create([
            'wedding_id' => $wedding->id,
            'user_id' => User::factory()->create()->id,
            'plan' => 'pro',
            'amount' => $amount,
            'proof_path' => 'proof/whatever.jpg',
            'status' => $status,
            'reviewed_at' => now()->subDay(),
        ]);
    }

    public function test_status_account_shows_the_purchase_history()
    {
        [$wedding, $user] = $this->couple();
        $this->request($wedding, 'approved', 55000);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Riwayat Pembelian')
            ->assertSee('Disetujui')
            ->assertSee('Rp 55.000');
    }

    public function test_pending_and_rejected_requests_also_appear()
    {
        [$wedding, $user] = $this->couple('free');
        $this->request($wedding, 'pending', 55000);
        $rejected = $this->request($wedding, 'rejected', 55000);
        $rejected->update(['admin_note' => 'Dana belum kami terima, mohon konfirmasi ke admin']);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Riwayat Pembelian')
            ->assertSee('Menunggu')
            ->assertSee('Ditolak')
            // Alasan penolakan ikut ditampilkan supaya pengguna tahu kenapa
            // tidak sampai Pro, tanpa harus thawed ke admin.
            ->assertSee('Dana belum kami terima');
    }

    public function test_history_does_not_leak_other_couples_purchases()
    {
        [$wedding, $user] = $this->couple();
        [$otherWedding] = $this->couple();
        $this->request($otherWedding, 'approved', 999000);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Riwayat Pembelian')
            ->assertDontSee('999.000');
    }

    public function test_history_block_is_hidden_when_there_is_no_purchase()
    {
        [, $user] = $this->couple();

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Riwayat Pembelian');
    }

    public function test_superadmin_without_wedding_sees_the_page_without_history()
    {
        $admin = User::factory()->create([
            'email' => 'superadmin-hist@example.com',
            'is_superadmin' => true,
        ]);

        // Superadmin sengaja tidak punya wedding, jadi relasinya null dan
        // controller harus tetap bisa mengirim halaman ini.
        $this->actingAs($admin)->get('/admin')->assertOk();
    }
}