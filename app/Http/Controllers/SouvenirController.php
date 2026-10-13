<?php

namespace App\Http\Controllers;

use App\Models\DropdownOption;
use App\Models\Souvenir;
use App\Models\Wedding;
use Illuminate\Http\Request;

class SouvenirController extends Controller
{
    /**
     * PRD Module 14 - Daftar Seserahan.
     *
     * Pengjxulan ke Budget Planner ditangani Souvenir::syncBudget() sebagai
     * aturan model, jadi controller ini tidak perlu urus budget.
     */
    public function index()
    {
        $wedding = Wedding::current();

        $souvenirs = $wedding->souvenirs()
            ->with('vendor')
            ->orderByRaw("CASE status WHEN 'Belum Dipilih' THEN 1 WHEN 'Sedang Dipilih' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->get();

        $summary = [
            'total' => $souvenirs->count(),
            'received' => $souvenirs->where('status', Souvenir::STATUS_RECEIVED)->count(),
            'planned' => (float) $souvenirs->sum('planned_price'),
            'actual' => (float) $souvenirs->where('status', Souvenir::STATUS_RECEIVED)->sum('actual_cost'),
        ];
        $summary['variance'] = $summary['planned'] - $summary['actual'];

        $statuses = DropdownOption::getOptions('souvenir_status');

        return view('souvenirs.index', compact('wedding', 'souvenirs', 'summary', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateItem($request);

        $wedding = Wedding::current();
        $wedding->souvenirs()->create($validated);

        return redirect()->route('souvenirs.index')
            ->with('success', 'Item seserahan ditambahkan.');
    }

    public function update(Request $request, Souvenir $souvenir)
    {
        $validated = $this->validateItem($request, partial: true);

        $souvenir->update($validated);

        return redirect()->route('souvenirs.index')
            ->with('success', 'Item seserahan diperbarui.');
    }

    public function destroy(Souvenir $souvenir)
    {
        // Baris Budget Planner ikut terhapus lewat cascadeOnDelete FK.
        $souvenir->delete();

        return redirect()->route('souvenirs.index')
            ->with('success', 'Item seserahan dihapus.');
    }

    private function validateItem(Request $request, bool $partial = false): array
    {
        // Kedua harga dikirim terformat ("Rp 250.000") dari input uang di view,
        // jadi digitsnya dibersihkan dulu sebelum aturan numeric jalan.
        $this->normalizeDigitInputs($request, ['planned_price', 'actual_cost']);

        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'name' => $required . '|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
            'link' => 'nullable|string|max:255',
            'photo' => 'nullable|string|max:255',
            // planned_price wajib punya batas atas: kolomnya decimal(15,2),
            // tanpa max angka kepanjangan lolos validasi lalu meledak jadi
            // QueryException SQLSTATE[22003].
            'planned_price' => $required . '|numeric|min:0|max:' . static::MONEY_MAX,
            'actual_cost' => $this->moneyRule(false),
            'status' => 'nullable|string|max:50',
            'received_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
    }
}