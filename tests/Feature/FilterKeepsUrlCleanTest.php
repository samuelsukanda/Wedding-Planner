<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman dengan filter tidak boleh menaruh state filter di query string.
 *
 * URL publik app ini sengaja dibuat bersih (selalu "/"), dan modul baru
 * sebelumnya sempat menulis filter ke URL sehingga muncul
 * "https://nupsera.my.id/?status=Todo&priority=Low".
 *
 * Test ini memeriksa sisi server: view tidak lagi membaca filter dari query
 * string, jadi filter yang ketemu di URL diabaikan dan daftar tetap utuh.
 * Sisi klien (Alpine tidak menulis URL) diverifikasi di browser.
 */
class FilterKeepsUrlCleanTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        $wedding = Wedding::create([
            'groom_name' => 'Groom',
            'bride_name' => 'Bride',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);

        return User::factory()->create(['wedding_id' => $wedding->id]);
    }

    public function test_guest_ignores_query_string_filters(): void
    {
        $user = $this->makeUser();

        $user->wedding->guests()->create([
            'name' => 'Bpk. Budi',
            'category' => 'Family',
            'attendance_status' => 'Attend',
        ]);

        // Query string di URL tidak boleh menyaring hasil.
        $this->actingAs($user)
            ->get('/guests?search=tidakada&category=Family&status=Decline')
            ->assertOk()
            ->assertSee('Bpk. Budi');
    }

    public function test_checklist_ignores_query_string_filters(): void
    {
        $user = $this->makeUser();

        $user->wedding->checklists()->create([
            'title' => 'Booking venue',
            'category' => 'Venue',
            'status' => 'Todo',
            'priority' => 'High',
        ]);

        $this->actingAs($user)
            ->get('/checklists?search=tidakada&status=Done&priority=Low')
            ->assertOk()
            ->assertSee('Booking venue');
    }

    public function test_vendor_ignores_query_string_filters(): void
    {
        $user = $this->makeUser();

        $user->wedding->vendors()->create([
            'name' => 'Dekorasi Jaya',
            'category' => 'Dekorasi',
            'booking_status' => 'Booked',
        ]);

        $this->actingAs($user)
            ->get('/vendors?search=tidakada&category=Catering&status=Cancelled')
            ->assertOk()
            ->assertSee('Dekorasi Jaya');
    }

    public function test_contract_ignores_query_string_filters(): void
    {
        $user = $this->makeUser();

        $vendor = $user->wedding->vendors()->create([
            'name' => 'Dekorasi Jaya',
            'category' => 'Dekorasi',
        ]);

        $user->wedding->vendorContracts()->create([
            'vendor_id' => $vendor->id,
            'contract_number' => 'KTR-001',
            'nominal' => 1000000,
            'dp_amount' => 300000,
            'final_amount' => 700000,
            'status' => 'Signed',
        ]);

        $this->actingAs($user)
            ->get('/contracts?search=tidakada&status=Draft')
            ->assertOk()
            ->assertSee('KTR-001');
    }

    public function test_payment_ignores_query_string_filters(): void
    {
        $user = $this->makeUser();

        $vendor = $user->wedding->vendors()->create([
            'name' => 'Dekorasi Jaya',
            'category' => 'Dekorasi',
        ]);

        $user->wedding->vendorPayments()->create([
            'vendor_id' => $vendor->id,
            'nominal' => 500000,
            'status' => 'Lunas',
            'notes' => 'DP professional',
        ]);

        $this->actingAs($user)
            ->get('/payments?search=tidakada&status=DP')
            ->assertOk()
            ->assertSee('DP professional');
    }

    /**
     * Halaman daftar harus bisa dibuka dengan query string apa pun tanpa
     * error. Ini menangkap kalau ada view yang masih membaca request() untuk
     * nilai yang sudah dihapus.
     */
    public function test_pages_render_with_arbitrary_query_string(): void
    {
        $user = $this->makeUser();

        foreach (['/guests', '/checklists', '/vendors', '/contracts', '/payments'] as $path) {
            $this->actingAs($user)
                ->get($path . '?status=Todo&priority=Low&search=' . urlencode('a b'))
                ->assertOk("Halaman {$path} harus tetap jalan dengan query string.");
        }
    }
}
