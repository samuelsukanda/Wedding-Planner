<?php

namespace Tests\Feature;

use App\Models\DocumentRequirement;
use App\Models\ProposalChecklist;
use App\Models\ProposalEvent;
use App\Models\SavingsGoal;
use App\Models\Souvenir;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportModulesTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email = 'report@example.com'): User
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

    private function seedModules(User $user): void
    {
        $wedding = $user->wedding;

        SavingsGoal::create([
            'wedding_id' => $wedding->id,
            'name' => 'Gedung',
            'target_amount' => 50000000,
            'initial_balance' => 10000000,
            'status' => 'Aktif',
        ]);

        ProposalEvent::create([
            'wedding_id' => $wedding->id,
            'event_date' => '2027-06-01',
            'location' => 'Jakarta',
            'theme' => 'Adat',
        ]);
        ProposalChecklist::create([
            'wedding_id' => $wedding->id,
            'title' => 'Booking tempat lamaran',
            'status' => 'Done',
        ]);

        Souvenir::create([
            'wedding_id' => $wedding->id,
            'name' => 'Kain Batik',
            'planned_price' => 500000,
            'actual_cost' => 450000,
            'status' => Souvenir::STATUS_RECEIVED,
        ]);
        Souvenir::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tas Anyam',
            'planned_price' => 300000,
            'actual_cost' => 0,
            'status' => 'Belum Dipilih',
        ]);

        DocumentRequirement::create([
            'wedding_id' => $wedding->id,
            'name' => 'Akta Lahir',
            'status' => 'Lengkap',
        ]);
        DocumentRequirement::create([
            'wedding_id' => $wedding->id,
            'name' => 'KTP',
            'status' => 'Belum Lengkap',
        ]);
    }

    public function test_report_shows_new_module_sections(): void
    {
        $user = $this->makeUser();
        $this->seedModules($user);

        $this->actingAs($user)->get('/reports')
            ->assertOk()
            ->assertSee('Rekap Tabungan')
            ->assertSee('Rekap Lamaran')
            ->assertSee('Progress Seserahan')
            ->assertSee('Progress Persyaratan Nikah')
            ->assertSee('1 dari 2 item diterima')
            ->assertSee('1 dari 2 dokumen lengkap');
    }

    public function test_report_print_styles_allow_content_to_span_pages_and_hide_mobile_navigation(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/reports')
            ->assertOk()
            ->assertSee('id="report-content"', false)
            ->assertSee('#page-content>div:not(#report-content)', false)
            ->assertSee('#report-content>.grid>div', false)
            ->assertSee('.report-print-card', false)
            ->assertSee('.mobile-bottom-nav', false);
    }

    public function test_export_contains_new_sheets(): void
    {
        $user = $this->makeUser('export@example.com');
        $this->seedModules($user);

        $res = $this->actingAs($user)->get('/reports/export');
        $res->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml.sheet',
            $res->headers->get('Content-Type')
        );

        $tmp = tempnam(sys_get_temp_dir(), 'rpt') . '.xlsx';
        file_put_contents($tmp, $res->streamedContent());
        try {
            $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);
            $this->assertEquals([
                'Ringkasan Laporan',
                'Rincian Budget',
                'Rincian Tabungan',
                'Rincian Lamaran',
                'Rincian Seserahan',
                'Rincian Persyaratan',
            ], $book->getSheetNames());

            $summary = $book->getSheetByName('Ringkasan Laporan')->toArray();
            $flat = implode("\n", array_map(fn ($row) => implode(' ', $row), $summary));
            $this->assertStringContainsString('REKAPITULASI TABUNGAN', $flat);
            $this->assertStringContainsString('REKAPITULASI LAMARAN', $flat);
            $this->assertStringContainsString('REKAPITULASI SESERAHAN', $flat);
            $this->assertStringContainsString('REKAPITULASI PERSYARATAN NIKAH', $flat);
        } finally {
            @unlink($tmp);
        }
    }

    public function test_dashboard_shows_souvenir_and_document_progress(): void
    {
        $user = $this->makeUser('dash@example.com');
        $this->seedModules($user);

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSee('Progress Seserahan')
            ->assertSee('Progress Persyaratan')
            ->assertSee('1 dari 2 diterima')
            ->assertSee('1 dari 2 lengkap');
    }
}
