<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

/**
 * Template Persyaratan Nikah yang dikelola superadmin.
 *
 * Semua route di controller ini dipasang di dalam middleware 'superadmin',
 * jadi gate aksesnya sudah ditangani di routes/web.php.
 */
class DocumentTemplateController extends Controller
{
    public function index()
    {
        $templates = DocumentTemplate::withCount('requirements')->orderBy('sort_order')->get();

        return view('admin.document-templates', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTemplate($request);

        $items = $this->parseItems($request->input('items_raw'));

        DocumentTemplate::create($validated + ['items' => $items]);

        return redirect()->route('admin.document-templates.index')
            ->with('success', 'Template dibuat.');
    }

    public function update(Request $request, DocumentTemplate $template)
    {
        $validated = $this->validateTemplate($request);

        if ($request->filled('items_raw')) {
            $validated['items'] = $this->parseItems($request->input('items_raw'));
        }

        $template->update($validated);

        return redirect()->route('admin.document-templates.index')
            ->with('success', 'Template diperbarui.');
    }

    public function destroy(DocumentTemplate $template)
    {
        // DocumentRequirement memakai nullOnDelete, jadi dokumen yang sudah
        // disalin ke pasangan lain tetap aman.
        $template->delete();

        return redirect()->route('admin.document-templates.index')
            ->with('success', 'Template dihapus.');
    }

    private function validateTemplate(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }

    /**
     * Accepts one document name per line. Blank lines are ignored.
     *
     * Format teks satu-per-baris lebih ringan dipakai daripada JSON di form
     * admin, dan superadmin tidak perlu menghitung kurung kurawal.
     *
     * @return list<array{name: string, status: string}>
     */
    private function parseItems(?string $raw): array
    {
        $lines = preg_split('/\R/', (string) $raw) ?: [];

        $items = [];

        foreach ($lines as $line) {
            $name = trim($line);

            if ($name === '') {
                continue;
            }

            $items[] = ['name' => $name, 'status' => 'Belum Lengkap'];
        }

        return $items;
    }
}