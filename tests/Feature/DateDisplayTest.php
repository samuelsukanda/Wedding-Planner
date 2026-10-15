<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Checklist;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Models\VendorContract;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dua kelas bug yang pernah muncul di halaman daftar:
 *
 * 1. Fallback tanggal memakai em-dash yang byte-nya rusak, jadi tampil sebagai
 *    "â€" alih-alih tanda hubung.
 * 2. currentItem diisi dengan json_encode($model), yang membaca atribut mentah
 *    dan melewati cast Eloquent. Akibatnya field tanggal berisi
 *    "2026-08-10 00:00:00" sehingga flatpickr tidak bisa mem-parse dan kolom
 *    tanggal kosong saat modal edit dibuka.
 */
class DateDisplayTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email = 'a@example.com'): User
    {
        $wedding = Wedding::create([
            'groom_name' => 'Groom',
            'bride_name' => 'Bride',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
            // Moodboard kini fitur Pro, jadi test ini perlu merender paket Pro
            // supaya seluruh halaman daftar tetap bisa dirender. Perilaku
            // lock-nya diuji terpisah di PlanTest.
            'plan' => 'pro',
        ]);

        return User::factory()->create(['email' => $email, 'wedding_id' => $wedding->id]);
    }

    /**
     * Setiap halaman daftar harus bisa dirender tanpa karakter rusak sama
     * sekali. Dicek pada byte mentah supaya karakter replacement pun ikut tertangkap.
     *
     * @dataProvider listPages
     */
    public function test_pages_render_without_mojibake(): void
    {
        // Loop biasa, bukan dataProvider: PHPUnit di proyek ini belum mendukung.
        foreach (self::listPages() as $path) {
            $user = $this->makeUser('user-' . md5((string) $path) . '@example.com');
            $html = $this->actingAs($user)->get($path)->assertOk()->getContent();

            $this->assertStringNotContainsString(
                "\xEF\xBF\xBD",
                $html,
                "Halaman {$path} memuat karakter replacement character."
            );

            $this->assertStringNotContainsString(
                "\xC3\xA2\xE2\x82\xAC\xE2\x80\x9D",
                $html,
                "Halaman {$path} memuat em-dash rusak."
            );
        }
    }

    public static function listPages(): array
    {
        return [
            '/checklists',
            '/budgets',
            '/vendors',
            '/guests',
            '/contracts',
            '/payments',
            '/savings',
            '/souvenirs',
            '/proposals',
            '/documents',
            '/moodboards',
            '/rundowns',
        ];
    }

    /**
     * Checklist tanpa deadline harus tetap tampil rapi, bukan karakter rusak.
     */
    public function test_checklist_without_deadline_renders_clean_fallback(): void
    {
        $user = $this->makeUser();

        Checklist::create([
            'wedding_id' => $user->wedding_id,
            'title' => 'Tanpa tenggat',
            'category' => 'General',
            'status' => 'Todo',
        ]);

        $html = $this->actingAs($user)->get('/checklists')->assertOk()->getContent();

        $this->assertStringContainsString('Tanpa tenggat', $html);
        $this->assertStringNotContainsString("\xC3\xA2\xE2\x82\xAC\xE2\x80\x9D", $html);
    }

    /**
     * Modal edit harus mengirim tanggal dalam format Y-m-d, bukan datetime.
     * json_encode($model) melewati cast dan menghasilkan "… 00:00:00" yang
     * tidak bisa dibaca flatpickr.
     */
    public function test_edit_modal_sends_plain_date_format(): void
    {
        $user = $this->makeUser();

        $item = Checklist::create([
            'wedding_id' => $user->wedding_id,
            'title' => 'Booking venue',
            'category' => 'Venue',
            'status' => 'Todo',
            'deadline' => '2026-08-10',
        ]);

        $html = $this->actingAs($user)->get('/checklists')->assertOk()->getContent();

        // Tanggal harus muncul sebagai Y-m-d di payload yang dikirim ke Alpine.
        $this->assertStringContainsString('2026-08-10', $html);
        $this->assertStringNotContainsString('2026-08-10 00:00:00', $html);
    }

    public function test_savings_edit_modal_sends_plain_date_format(): void
    {
        $user = $this->makeUser();

        SavingsGoal::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Dana venue',
            'target_amount' => 50000000,
            'target_date' => '2027-01-15',
        ]);

        $html = $this->actingAs($user)->get('/savings')->assertOk()->getContent();

        $this->assertStringContainsString('2027-01-15', $html);
        $this->assertStringNotContainsString('2027-01-15 00:00:00', $html);
    }

    public function test_contract_edit_modal_sends_plain_date_format(): void
    {
        $user = $this->makeUser();

        $vendor = $user->wedding->vendors()->create([
            'name' => 'Dekorasi Jaya',
            'category' => 'Dekorasi',
        ]);

        VendorContract::create([
            'wedding_id' => $user->wedding_id,
            'vendor_id' => $vendor->id,
            'contract_number' => 'K-001',
            'nominal' => 5000000,
            'due_date' => '2026-09-20',
            'status' => 'Draft',
        ]);

        $html = $this->actingAs($user)->get('/contracts')->assertOk()->getContent();

        $this->assertStringContainsString('2026-09-20', $html);
        $this->assertStringNotContainsString('2026-09-20 00:00:00', $html);
    }

    /**
     * Field tanggal bertipe native (type="date") tidak bisa diformat DD-MM-YYYY
     * dan flatpickr tidak berjalan di atasnya. Semua field tanggal harus
     * type="text" dengan class datepicker.
     */
    public function test_date_inputs_use_text_with_datepicker(): void
    {
        $user = $this->makeUser();

        foreach (['/checklists', '/contracts', '/payments', '/savings', '/documents', '/souvenirs', '/proposals'] as $path) {
            $html = $this->actingAs($user)->get($path)->assertOk()->getContent();

            $this->assertDoesNotMatchRegularExpression(
                '/<input[^>]*type="date"[^>]*>/',
                $html,
                "Halaman {$path} masih memakai input type=\"date\" yang tidak bisa diformat."
            );
        }
    }

    /**
     * Sebaliknya, field bebas (bukan tanggal) tidak boleh punya class
     * datepicker. Lokasi dan Tema acara pernah ikut dapat class itu sehingga
     * mengetik teks bebas malah memunculkan kalender.
     */
    public function test_free_text_fields_do_not_use_datepicker(): void
    {
        $user = $this->makeUser();

        $html = $this->actingAs($user)->get('/proposals')->assertOk()->getContent();

        foreach (['location', 'theme'] as $field) {
            // Ambil blok tag input untuk field ini; tag bisa berisi "->" di
            // ekspresi Blade, jadi dipisah manual, bukan regex [^>]*.
            $pos = strpos($html, 'name="' . $field . '"');

            $this->assertNotFalse($pos, "Field {$field} tidak ditemukan di halaman Lamaran.");

            $start = strrpos(substr($html, 0, $pos), '<input');
            $tag = substr($html, $start, $pos - $start + 200);

            $this->assertStringNotContainsString(
                'datepicker',
                $tag,
                "Field {$field} bukan field tanggal tapi memakai class datepicker."
            );
        }
    }

    /**
     * src file view tidak boleh memuat em-dash rusak sedikit pun.
     */
    public function test_source_views_have_no_mojibake(): void
    {
        $broken = "\xC3\xA2\xE2\x82\xAC\xE2\x80\x9D";

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );

        $offenders = [];

        foreach ($files as $file) {
            if ($file->isDir() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            if (str_contains((string) file_get_contents($file->getPathname()), $broken)) {
                $offenders[] = $file->getFilename();
            }
        }

        $this->assertSame([], $offenders, 'View masih punya em-dash rusak: ' . implode(', ', $offenders));
    }
}
