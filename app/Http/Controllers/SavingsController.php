<?php

namespace App\Http\Controllers;

use App\Models\DropdownOption;
use App\Models\Notification;
use App\Models\SavingsGoal;
use App\Models\SavingsTransaction;
use App\Models\Wedding;
use Illuminate\Http\Request;

class SavingsController extends Controller
{
    public function index()
    {
        $wedding = Wedding::current();

        $goals = $wedding->savingsGoals()
            ->withCount('transactions')
            // CASE (bukan FIELD) supaya query jalan di MySQL dan SQLite test.
            ->orderByRaw("CASE status WHEN 'Aktif' THEN 1 WHEN 'Ditunda' THEN 2 WHEN 'Tercapai' THEN 3 ELSE 4 END")
            ->orderBy('target_date')
            ->get();

        // PRD: Grafik perkembangan tabungan â€” saldo kumulatif per tanggal.
        $allTransactions = $wedding->savingsTransactions()
            ->with('goal')
            ->orderBy('transaction_date')
            ->get();

        $chart = $this->buildChart($wedding, $allTransactions);

        $summary = [
            'totalTarget' => (float) $goals->sum('target_amount'),
            'totalBalance' => (float) $goals->sum('current_balance'),
            'totalShortfall' => (float) $goals->sum(fn ($g) => $g->shortfall()),
            'activeGoals' => $goals->where('status', 'Aktif')->count(),
        ];

        $statuses = DropdownOption::getOptions('savings_status', ['Aktif', 'Tercapai', 'Ditunda']);
        $frequencies = DropdownOption::getOptions('savings_frequency', ['Mingguan', 'Bulanan', 'Tahunan']);

        return view('savings.index', compact(
            'wedding',
            'goals',
            'allTransactions',
            'chart',
            'summary',
            'statuses',
            'frequencies'
        ));
    }

    public function store(Request $request)
    {
        $wedding = Wedding::current();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1',
            'initial_balance' => 'nullable|numeric|min:0',
            'periodic_amount' => 'nullable|numeric|min:0',
            'frequency' => 'nullable|string',
            'target_date' => 'nullable|date',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['initial_balance'] = $validated['initial_balance'] ?? 0;
        $validated['periodic_amount'] = $validated['periodic_amount'] ?? 0;
        $validated['current_balance'] = $validated['initial_balance'];
        $validated['status'] = $validated['status'] ?? 'Aktif';

        $goal = $wedding->savingsGoals()->create($validated);
        $goal->recalculate();

        return redirect()->route('savings.index')
            ->with('success', 'Target tabungan berhasil dibuat!');
    }

    public function update(Request $request, SavingsGoal $savingsGoal)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1',
            'initial_balance' => 'nullable|numeric|min:0',
            'periodic_amount' => 'nullable|numeric|min:0',
            'frequency' => 'nullable|string',
            'target_date' => 'nullable|date',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Saldo awal mengunci begitu ada transaksi (lihat SavingsGoal::hasTransactions).
        if ($savingsGoal->hasTransactions()) {
            unset($validated['initial_balance']);
        } else {
            $validated['initial_balance'] = $validated['initial_balance'] ?? 0;
        }

        $validated['periodic_amount'] = $validated['periodic_amount'] ?? 0;
        $validated['status'] = $validated['status'] ?? 'Aktif';

        $savingsGoal->update($validated);
        $savingsGoal->recalculate();

        return redirect()->route('savings.index')
            ->with('success', 'Target tabungan berhasil diperbarui!');
    }

    public function destroy(SavingsGoal $savingsGoal)
    {
        $savingsGoal->delete();

        return redirect()->route('savings.index')
            ->with('success', 'Target tabungan berhasil dihapus!');
    }

    /**
     * PRD: Setoran manual dan rutin + Riwayat transaksi.
     */
    public function storeTransaction(Request $request, SavingsGoal $savingsGoal)
    {
        $validated = $request->validate([
            'type' => 'required|in:' . SavingsGoal::TYPE_SETORAN . ',' . SavingsGoal::TYPE_PENARIKAN,
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Business Rule 2: penarikan tidak boleh melebihi saldo saat ini.
        // Dicek di server, bukan hanya disables tombol di UI.
        // Semua redirect diarahkan eksplisit ke route modul, bukan back(). URL
        // internal sengaja disembunyikan sebagai "/", jadi back() selalu
        // mengarah ke root dan user diminta pindah ke Dashboard.
        if ($validated['type'] === SavingsGoal::TYPE_PENARIKAN
            && (float) $validated['amount'] > (float) $savingsGoal->current_balance) {
            return redirect()->route('savings.index')->withErrors([
                'amount' => 'Penarikan tidak boleh melebihi saldo saat ini (Rp '
                    . number_format((float) $savingsGoal->current_balance, 0, ',', '.') . ').',
            ]);
        }

        // wedding_id harus diisi eksplisit: relasi hasMany hanya mengisi
        // foreign key (savings_goal_id), bukan kolom tenant.
        $savingsGoal->transactions()->create(array_merge($validated, [
            'wedding_id' => $savingsGoal->wedding_id,
        ]));
        $savingsGoal->recalculate();

        $this->scheduleReminder($savingsGoal);

        $label = $validated['type'] === SavingsGoal::TYPE_PENARIKAN ? 'Penarikan' : 'Setoran';

        return redirect()->route('savings.index')
            ->with('success', $label . ' tabungan berhasil dicatat!');
    }

    public function destroyTransaction(SavingsGoal $savingsGoal, SavingsTransaction $transaction)
    {
        abort_unless($transaction->savings_goal_id === $savingsGoal->id, 404);

        $transaction->delete();
        $savingsGoal->recalculate();

        return redirect()->route('savings.index')
            ->with('success', 'Transaksi tabungan berhasil dihapus!');
    }

    /**
     * PRD Fitur: Pengingat setoran (Business Rule 6).
     *
     * memakai tabel notifications yang sudah ada â€” tidak menambah tabel baru.
     */
    private function scheduleReminder(SavingsGoal $goal): void
    {
        $due = $goal->nextDueDate();

        if (! $due) {
            return;
        }

        Notification::updateOrCreate(
            [
                'wedding_id' => $goal->wedding_id,
                'type' => 'savings_due',
                'reminder_date' => $due,
            ],
            [
                'title' => 'Setoran tabungan: ' . $goal->name,
                'message' => 'Jatuh tempo setoran berikutnya. Target per periode Rp '
                    . number_format((float) $goal->periodic_amount, 0, ',', '.') . '.',
                'is_read' => false,
            ]
        );
    }

    /**
     * Data grafik: saldo kumulatif seluruh target, satu titik per tanggal transaksi.
     */
    private function buildChart(Wedding $wedding, $allTransactions): array
    {
        $balance = (float) $wedding->savingsGoals()->sum('initial_balance');
        $labels = [];
        $values = [];

        // Titik awal: saldo awal kumulatif sebelum transaksi pertama.
        $labels[] = 'Awal';
        $values[] = round($balance, 0);

        foreach ($allTransactions as $transaction) {
            $amount = (float) $transaction->amount;
            $balance += $transaction->isPenarikan() ? -$amount : $amount;

            $labels[] = $transaction->transaction_date->format('d M Y');
            $values[] = round($balance, 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}