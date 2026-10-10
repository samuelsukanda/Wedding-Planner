<?php

namespace Tests\Feature;

use App\Models\DocumentRequirement;
use App\Models\DocumentTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperadmin(): User
    {
        return User::factory()->create([
            'email' => 'admin@test.local',
            'is_superadmin' => true,
            'wedding_id' => null,
        ]);
    }

    private function makeCouple(): User
    {
        $wedding = Wedding::create([
            'groom_name' => 'Groom',
            'bride_name' => 'Bride',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);

        return User::factory()->create([
            'email' => 'couple@test.local',
            'is_superadmin' => false,
            'wedding_id' => $wedding->id,
        ]);
    }

    public function test_superadmin_can_create_template_with_items(): void
    {
        $admin = $this->makeSuperadmin();

        $this->actingAs($admin)->post('/admin/document-templates', [
            'name' => 'Persyaratan KUA',
            'description' => 'Dokumen standar KUA',
            'items_raw' => "Akta Lahir Suami\nAkta Lahir Istri\n\nBuku Nikah",
            'is_active' => 1,
        ])->assertRedirect(route('admin.document-templates.index'));

        $template = DocumentTemplate::first();
        $this->assertNotNull($template);
        $this->assertCount(3, $template->items);
        $this->assertSame('Akta Lahir Suami', $template->items[0]['name']);
        // Baris kosong harus dilewati.
        $this->assertSame('Buku Nikah', $template->items[2]['name']);
    }

    public function test_non_superadmin_is_blocked(): void
    {
        $couple = $this->makeCouple();

        $this->actingAs($couple)->get('/admin/document-templates')->assertForbidden();
        $this->actingAs($couple)->post('/admin/document-templates', ['name' => 'X'])->assertForbidden();
    }

    public function test_superadmin_can_update_and_delete_template(): void
    {
        $admin = $this->makeSuperadmin();

        $template = DocumentTemplate::create([
            'name' => 'Template Lama',
            'items' => [['name' => 'Dokumen A', 'status' => 'Belum Lengkap']],
        ]);

        $this->actingAs($admin)->put('/admin/document-templates/' . $template->id, [
            'name' => 'Template Baru',
            'items_raw' => "Dokumen A\nDokumen B",
        ])->assertRedirect(route('admin.document-templates.index'));

        $template->refresh();
        $this->assertSame('Template Baru', $template->name);
        $this->assertCount(2, $template->items);

        $this->actingAs($admin)->delete('/admin/document-templates/' . $template->id)
            ->assertRedirect(route('admin.document-templates.index'));

        $this->assertDatabaseCount('document_templates', 0);
    }

    /**
     * Dokumen yang sudah disalin pasangan harus aman kalau template dihapus.
     */
    public function test_deleting_template_keeps_copied_requirements(): void
    {
        $admin = $this->makeSuperadmin();
        $couple = $this->makeCouple();

        $template = DocumentTemplate::create([
            'name' => 'Template',
            'items' => [['name' => 'Dokumen Penting', 'status' => 'Belum Lengkap']],
        ]);

        $requirement = DocumentRequirement::create([
            'wedding_id' => $couple->wedding_id,
            'document_template_id' => $template->id,
            'name' => 'Dokumen Penting',
        ]);

        $this->actingAs($admin)->delete('/admin/document-templates/' . $template->id);

        $this->assertDatabaseHas('document_requirements', ['id' => $requirement->id]);
        $this->assertNull($requirement->fresh()->document_template_id);
    }

    public function test_copy_to_wedding_returns_count(): void
    {
        $couple = $this->makeCouple();

        $template = DocumentTemplate::create([
            'name' => 'Template',
            'items' => [
                ['name' => 'Satu', 'status' => 'Belum Lengkap'],
                ['name' => 'Dua', 'status' => 'Belum Lengkap'],
            ],
        ]);

        $count = $template->copyToWedding($couple->wedding);

        $this->assertSame(2, $count);
        $this->assertSame(2, DocumentRequirement::where('wedding_id', $couple->wedding_id)->count());
    }
}