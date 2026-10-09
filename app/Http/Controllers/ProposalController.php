<?php

namespace App\Http\Controllers;

use App\Models\DropdownOption;
use App\Models\ProposalBudget;
use App\Models\ProposalChecklist;
use App\Models\ProposalEvent;
use App\Models\ProposalGuest;
use App\Models\Vendor;
use App\Models\Wedding;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    /**
     * PRD Module 13 - Lamaran.
     *
     * Satu acara per wedding, jadi event dibuat once lalu di-update. Tab
     * Checklist / Budget / Tamu berdiri sendiri; Vendor, Rundown, dan
     * Dokumentasi memakai tabel existing dengan proposal_event_id.
     */
    public function index()
    {
        $wedding = Wedding::current();
        $event = ProposalEvent::firstOrCreate(['wedding_id' => $wedding->id]);

        $checklists = $event->checklists()->orderByRaw("CASE status WHEN 'Todo' THEN 1 WHEN 'Progress' THEN 2 ELSE 3 END")
            ->orderBy('deadline')
            ->get();

        $guests = $event->guests()->orderBy('name')->get();

        // Vendor: yang ditandai untuk lamaran + kandidat yang belum ditandai.
        $lamaranVendors = $event->vendors()->orderBy('name')->get();
        $otherVendors = $wedding->vendors()->whereNull('proposal_event_id')->orderBy('name')->get();

        $budgets = $event->budgets()->with('vendor')->orderBy('category')->get();

        // Rundown & dokumentasi lamaran (tabel existing).
        $rundown = $event->rundownEvents()->orderBy('time')->get();
        $moodboards = $event->moodboards()->orderBy('id')->get();

        $checklistSummary = [
            'total' => $checklists->count(),
            'done' => $checklists->where('status', 'Done')->count(),
            'overdue' => $checklists->filter(fn ($c) => $c->isOverdue())->count(),
        ];

        $guestSummary = [
            'rows' => $guests->count(),
            'pax' => (int) $guests->sum('guest_count'),
            'attend' => $guests->where('attendance_status', 'Attend')->count(),
        ];

        // Business Rule 4: budget lamaran terpisah, tapi ikut terhitung di
        // ringkasan total biaya persiapan bersama budget resepsi.
        $budgetSummary = [
            'planned' => (float) $budgets->sum('planned_budget'),
            'actual' => (float) $budgets->sum('actual_cost'),
        ];
        $budgetSummary['variance'] = $budgetSummary['planned'] - $budgetSummary['actual'];

        // Ringkasan total biaya persiapan = resepsi + lamaran, memakai nominal
        // rencana keduanya. Actual tidak ikut ditambah karena sudah bagian dari planned.
        $receptionPlanned = (float) $wedding->budgets()->sum('planned_budget');
        $weddingBudgetTotal = $receptionPlanned + $budgetSummary['planned'];

        return view('proposals.index', compact(
            'wedding',
            'event',
            'checklists',
            'guests',
            'lamaranVendors',
            'otherVendors',
            'budgets',
            'rundown',
            'moodboards',
            'checklistSummary',
            'guestSummary',
            'budgetSummary',
            'weddingBudgetTotal'
        ));
    }

    /**
     * Informasi acara: tanggal, waktu, lokasi, tema, catatan kebutuhan.
     */
    public function updateEvent(Request $request)
    {
        $validated = $request->validate([
            'event_date' => 'nullable|date',
            'event_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'theme' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $event = ProposalEvent::firstOrCreate(['wedding_id' => Wedding::current()->id]);
        $event->update($validated);

        return redirect()->route('proposals.index')
            ->with('success', 'Informasi acara lamaran disimpan.');
    }

    public function storeChecklist(Request $request)
    {
        $wedding = Wedding::current();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
            'priority' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $wedding->proposalChecklists()->create($validated);

        return redirect()->route('proposals.index')
            ->with('success', 'Item checklist ditambahkan.');
    }

    public function updateChecklist(Request $request, ProposalChecklist $checklist)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
            'priority' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $checklist->update($validated);

        return redirect()->route('proposals.index')
            ->with('success', 'Item checklist diperbarui.');
    }

    public function destroyChecklist(ProposalChecklist $checklist)
    {
        $checklist->delete();

        return redirect()->route('proposals.index')
            ->with('success', 'Item checklist dihapus.');
    }

    public function storeGuest(Request $request)
    {
        $wedding = Wedding::current();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:50',
            'relation' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'guest_count' => 'nullable|integer|min:1',
            'attendance_status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['guest_count'] = $validated['guest_count'] ?? 1;

        $wedding->proposalGuests()->create($validated);

        return redirect()->route('proposals.index')
            ->with('success', 'Tunamuan ditambahkan.');
    }

    public function updateGuest(Request $request, ProposalGuest $guest)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:50',
            'relation' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'guest_count' => 'nullable|integer|min:1',
            'attendance_status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['guest_count'] = $validated['guest_count'] ?? 1;

        $guest->update($validated);

        return redirect()->route('proposals.index')
            ->with('success', 'Tamu diperbarui.');
    }

    public function destroyGuest(ProposalGuest $guest)
    {
        $guest->delete();

        return redirect()->route('proposals.index')
            ->with('success', 'Tamu dihapus.');
    }

    public function storeBudget(Request $request)
    {
        $wedding = Wedding::current();

        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'item_name' => 'required|string|max:255',
            'planned_budget' => 'required|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'vendor_id' => 'nullable|exists:vendors,id',
            'notes' => 'nullable|string',
        ]);

        $validated['actual_cost'] = $validated['actual_cost'] ?? 0;

        $wedding->proposalBudgets()->create($validated);

        return redirect()->route('proposals.index')
            ->with('success', 'Budget lamaran ditambahkan.');
    }

    public function updateBudget(Request $request, ProposalBudget $budget)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'item_name' => 'required|string|max:255',
            'planned_budget' => 'required|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'vendor_id' => 'nullable|exists:vendors,id',
            'notes' => 'nullable|string',
        ]);

        $validated['actual_cost'] = $validated['actual_cost'] ?? 0;

        $budget->update($validated);

        return redirect()->route('proposals.index')
            ->with('success', 'Budget lamaran diperbarui.');
    }

    public function destroyBudget(ProposalBudget $budget)
    {
        $budget->delete();

        return redirect()->route('proposals.index')
            ->with('success', 'Budget lamaran dihapus.');
    }

    /**
     * Tandai vendor sebagai milik acara lamaran, atau lepaskan kembali ke daftar
     * umum. Satu aksi toggle, bukan dua form.
     */
    public function toggleVendor(Vendor $vendor)
    {
        if ($vendor->proposal_event_id) {
            $vendor->update(['proposal_event_id' => null]);

            return redirect()->route('proposals.index')
                ->with('success', "{$vendor->name} dikembalikan ke daftar vendor umum.");
        }

        $event = ProposalEvent::firstOrCreate(['wedding_id' => Wedding::current()->id]);
        $vendor->update(['proposal_event_id' => $event->id]);

        return redirect()->route('proposals.index')
            ->with('success', "{$vendor->name} ditandai untuk acara lamaran.");
    }
}