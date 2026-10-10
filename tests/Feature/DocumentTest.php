<?php

namespace Tests\Feature;

use App\Models\DocumentHistory;
use App\Models\DocumentRequirement;
use App\Models\DocumentTemplate;
use App\Models\Notification;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTest extends TestCase
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

    private function makeTemplate(): DocumentTemplate
    {
        return DocumentTemplate::create([
            'name' => 'Persyaratan KUA',
            'items' => [
                ['name' => 'Akta Lahir Suami', 'status' => 'Belum Lengkap'],
                ['name' => 'Akta Lahir Istri', 'status' => 'Belum Lengkap'],
            ],
            'is_active' => true,
        ]);
    }

    public function test_index_renders(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/documents')
            ->assertOk()
            ->assertSee('Persyaratan Nikah');
    }

    public function test_document_can_be_created(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/documents', [
            'name' => 'Akta Lahir Suami',
            'pic_name' => 'Bpk. Budi',
            'phone' => '08123456789',
            'deadline' => '2026-12-01',
            'status' => 'Belum Lengkap',
        ])->assertRedirect(route('documents.index'));

        $this->assertDatabaseHas('document_requirements', [
            'wedding_id' => $user->wedding_id,
            'name' => 'Akta Lahir Suami',
            'pic_name' => 'Bpk. Budi',
        ]);
    }

    public function test_name_is_required(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/documents', ['status' => 'Lengkap'])
            ->assertSessionHasErrors('name');
    }

    public function test_overdue_and_due_soon_are_detected(): void
    {
        $user = $this->makeUser();

        $late = DocumentRequirement::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Terlambat',
            'deadline' => now()->subDay()->toDateString(),
            'status' => 'Belum Lengkap',
        ]);

        $soon = DocumentRequirement::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Segera',
            'deadline' => now()->addDays(3)->toDateString(),
            'status' => 'Belum Lengkap',
        ]);

        $far = DocumentRequirement::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Nanti',
            'deadline' => now()->addDays(60)->toDateString(),
            'status' => 'Belum Lengkap',
        ]);

        $done = DocumentRequirement::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Selesai',
            'deadline' => now()->subDay()->toDateString(),
            'status' => 'Lengkap',
        ]);

        $this->assertTrue($late->isOverdue());
        $this->assertSame('Terlambat', $late->statusLabel());

        $this->assertTrue($soon->isDueSoon());
        $this->assertFalse($soon->isOverdue());

        $this->assertFalse($far->isDueSoon());
        $this->assertFalse($far->isOverdue());

        // Sudah lengkap tidak dihitung terlambat walau lewat tanggal.
        $this->assertFalse($done->isOverdue());
        $this->assertTrue($done->isComplete());
        $this->assertSame('Lengkap', $done->statusLabel());
    }

    /**
     * PRD: pengingat item deadline. Item yang mendekati deadline harus
     * otomatis jadi notifikasi.
     */
    public function test_due_soon_item_creates_reminder_notification(): void
    {
        $user = $this->makeUser();

        $item = DocumentRequirement::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Akta Lahir',
            'deadline' => now()->addDays(3)->toDateString(),
            'status' => 'Belum Lengkap',
        ]);

        $this->assertDatabaseHas('notifications', [
            'wedding_id' => $user->wedding_id,
            'type' => 'dokumen_deadline',
            'title' => 'Akta Lahir',
        ]);

        // Item yang selesai tidak butuh pengingat: notifikasi dibersihkan.
        $item->update(['status' => 'Lengkap']);

        $this->assertDatabaseMissing('notifications', ['title' => 'Akta Lahir']);
    }

    public function test_far_deadline_does_not_create_notification(): void
    {
        $user = $this->makeUser();

        DocumentRequirement::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Dokumen Jauh',
            'deadline' => now()->addDays(90)->toDateString(),
            'status' => 'Belum Lengkap',
        ]);

        $this->assertDatabaseCount('notifications', 0);
    }

    /**
     * PRD: riwayat. Perubahan status harus meninggalkan jejak.
     */
    public function test_status_change_is_logged_to_history(): void
    {
        $user = $this->makeUser();

        $item = DocumentRequirement::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Buku Nikah',
            'status' => 'Belum Lengkap',
        ]);

        $this->assertDatabaseCount('document_histories', 0);

        $this->actingAs($user)->put('/documents/' . $item->id, [
            'name' => 'Buku Nikah',
            'status' => 'Diproses',
        ])->assertRedirect(route('documents.index'));

        $this->assertDatabaseHas('document_histories', [
            'document_requirement_id' => $item->id,
            'status' => 'Diproses',
        ]);

        // Edit tanpa perubahan status tidak menambah riwayat.
        $this->actingAs($user)->put('/documents/' . $item->id, [
            'name' => 'Buku Nikah (rev)',
            'status' => 'Diproses',
        ]);

        $this->assertSame(1, DocumentHistory::where('document_requirement_id', $item->id)->count());
    }

    public function test_history_page_is_reachable(): void
    {
        $user = $this->makeUser();

        $item = DocumentRequirement::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Pas Foto',
            'status' => 'Belum Lengkap',
        ]);

        $this->actingAs($user)->get('/documents/' . $item->id . '/history')
            ->assertOk()
            ->assertSee('Pas Foto');
    }

    public function test_template_can_be_applied_to_wedding(): void
    {
        $user = $this->makeUser();
        $template = $this->makeTemplate();

        $this->actingAs($user)->post('/documents/templates/' . $template->id)
            ->assertRedirect(route('documents.index'));

        $this->assertDatabaseCount('document_requirements', 2);
        $this->assertDatabaseHas('document_requirements', [
            'name' => 'Akta Lahir Suami',
            'document_template_id' => $template->id,
        ]);
    }

    /**
     * Menerapkan template dua kali tidak boleh menggandakan dokumen.
     */
    public function test_applying_template_twice_does_not_duplicate(): void
    {
        $user = $this->makeUser();
        $template = $this->makeTemplate();

        $this->actingAs($user)->post('/documents/templates/' . $template->id);
        $this->actingAs($user)->post('/documents/templates/' . $template->id);

        $this->assertDatabaseCount('document_requirements', 2);
    }

    public function test_other_wedding_documents_are_invisible(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser('b@example.com');

        $otherItem = DocumentRequirement::create([
            'wedding_id' => $other->wedding_id,
            'name' => 'Dokumen Rahasia',
        ]);

        DocumentRequirement::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Dokumen Saya',
        ]);

        $this->actingAs($user)->get('/documents')
            ->assertOk()
            ->assertSee('Dokumen Saya')
            ->assertDontSee('Dokumen Rahasia');

        $this->actingAs($user)->delete('/documents/' . $otherItem->id)
            ->assertNotFound();

        $this->actingAs($user)->put('/documents/' . $otherItem->id, [
            'name' => 'Dibajak',
        ])->assertNotFound();
    }

    public function test_notification_scope_is_per_wedding(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser('b@example.com');

        DocumentRequirement::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Milik Saya',
            'deadline' => now()->addDays(2)->toDateString(),
        ]);

        DocumentRequirement::create([
            'wedding_id' => $other->wedding_id,
            'name' => 'Milik Train',
            'deadline' => now()->addDays(2)->toDateString(),
        ]);

        $this->assertSame(1, Notification::where('wedding_id', $user->wedding_id)->count());
        $this->assertSame(1, Notification::where('wedding_id', $other->wedding_id)->count());
    }
}