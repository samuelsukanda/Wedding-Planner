<?php

namespace App\Http\Controllers;

use App\Models\DocumentHistory;
use App\Models\DocumentRequirement;
use App\Models\DocumentTemplate;
use App\Models\DropdownOption;
use App\Models\Wedding;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * PRD Module 15 - Persyaratan Nikah.
     *
     * Riwayat dan pengingat ditangani DocumentRequirement sebagai aturan
     * model, jadi controller ini tidak perlu urus keduanya.
     */
    public function index()
    {
        $wedding = Wedding::current();

        $requirements = $wedding->documentRequirements()
            ->orderByRaw("CASE status WHEN 'Belum Lengkap' THEN 1 WHEN 'Diproses' THEN 2 ELSE 3 END")
            ->orderBy('deadline')
            ->get();

        $summary = [
            'total' => $requirements->count(),
            'complete' => $requirements->filter(fn ($r) => $r->isComplete())->count(),
            'overdue' => $requirements->filter(fn ($r) => $r->isOverdue())->count(),
            'dueSoon' => $requirements->filter(fn ($r) => $r->isDueSoon())->count(),
        ];

        $percent = $summary['total'] > 0
            ? round(($summary['complete'] / $summary['total']) * 100)
            : 0;

        $recentHistories = DocumentHistory::with('requirement')
            ->whereNotNull('document_requirement_id')
            ->latest()
            ->limit(10)
            ->get();

        $statuses = DropdownOption::getOptions('document_status');
        $templates = DocumentTemplate::where('is_active', true)->orderBy('sort_order')->get();

        return view('documents.index', compact(
            'wedding',
            'requirements',
            'summary',
            'percent',
            'recentHistories',
            'statuses',
            'templates'
        ));
    }

    /**
     * Salin template master ke daftar syarat pasangan ini. Berguna kalau
     * superadmin menambah dokumen baru setelah pasangan ini dibuat.
     */
    public function applyTemplate(DocumentTemplate $template)
    {
        $wedding = Wedding::current();

        // Jangan dobel: lewati dokumen dengan nama yang sudah ada.
        $existing = $wedding->documentRequirements()->pluck('name');
        $toAdd = collect($template->items ?? [])
            ->reject(fn ($item) => $existing->contains($item['name'] ?? null));

        foreach ($toAdd as $item) {
            DocumentRequirement::create([
                'wedding_id' => $wedding->id,
                'document_template_id' => $template->id,
                'name' => $item['name'],
                'status' => $item['status'] ?? DocumentRequirement::STATUS_DEFAULT,
            ]);
        }

        if ($toAdd->isEmpty()) {
            return redirect()->route('documents.index')
                ->with('success', 'Semua dokumen dari template ini sudah ada di daftar Anda.');
        }

        return redirect()->route('documents.index')
            ->with('success', $toAdd->count() . ' dokumen ditambahkan dari template.');
    }

    public function store(Request $request)
    {
        $validated = $this->validateItem($request);

        Wedding::current()->documentRequirements()->create($validated);

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen ditambahkan.');
    }

    public function update(Request $request, DocumentRequirement $requirement)
    {
        $validated = $this->validateItem($request, partial: true);

        // Catat riwayat kalau status berubah, bukan tiap edit kecil.
        $statusChanged = isset($validated['status'])
            && $validated['status'] !== $requirement->status;

        $requirement->update($validated);

        if ($statusChanged) {
            $requirement->logHistory('Status diubah menjadi ' . $requirement->status . '.');
        }

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen diperbarui.');
    }

    public function destroy(DocumentRequirement $requirement)
    {
        $requirement->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen dihapus.');
    }

    public function history(DocumentRequirement $requirement)
    {
        $histories = $requirement->histories()->latest()->get();

        return view('documents.history', [
            'requirement' => $requirement,
            'histories' => $histories,
        ]);
    }

    private function validateItem(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'name' => $required . '|string|max:255',
            'pic_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'document_file' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
    }
}