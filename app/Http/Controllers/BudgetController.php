<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\Budget;
use App\Models\Vendor;
use App\Models\DropdownOption;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $wedding = Wedding::current();
        $budgets = $wedding->budgets()->with('vendor')->get();

        // Auto calculate stats
        $totalPlanned = $budgets->sum('planned_budget');
        $totalActual = $budgets->sum('actual_cost');
        $totalRemaining = max(0, $wedding->total_budget - $totalActual);

        // Chart Data (Budget per Category)
        $chartData = $budgets->groupBy('category')->map(function ($items) {
            return [
                'planned' => $items->sum('planned_budget'),
                'actual' => $items->sum('actual_cost'),
            ];
        });

        $categories = DropdownOption::getOptions('budget_category', [
            'Gedung', 'Catering', 'Dekorasi', 'Fotografer', 'Videografer',
            'MUA', 'Busana', 'Souvenir', 'Undangan', 'Transportasi', 'Lainnya'
        ]);

        // Get all vendors grouped by category for the auto-fill feature
        $vendorsJson = Vendor::where('wedding_id', $wedding->id)
            ->whereNotNull('package')
            ->get(['id', 'name', 'category', 'package', 'price'])
            ->groupBy('category')
            ->toJson();

        return view('budgets.index', compact(
            'wedding',
            'budgets',
            'totalPlanned',
            'totalActual',
            'totalRemaining',
            'chartData',
            'categories',
            'vendorsJson'
        ));
    }

    public function store(Request $request)
    {
        $wedding = Wedding::current();
        $validated = $request->validate([
            'category' => 'required|string',
            'item_name' => 'required|string|max:255',
            'planned_budget' => 'required|numeric|min:0',
            'actual_cost' => 'required|numeric|min:0',
            'vendor_id' => 'nullable|exists:vendors,id',
            'notes' => 'nullable|string',
        ]);

        $wedding->budgets()->create($validated);

        return redirect()->route('budgets.index')->with('success', 'Anggaran berhasil ditambahkan!');
    }

    public function update(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'item_name' => 'required|string|max:255',
            'planned_budget' => 'required|numeric|min:0',
            'actual_cost' => 'required|numeric|min:0',
            'vendor_id' => 'nullable|exists:vendors,id',
            'notes' => 'nullable|string',
        ]);

        $budget->update($validated);

        return redirect()->route('budgets.index')->with('success', 'Anggaran berhasil diperbarui!');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->route('budgets.index')->with('success', 'Anggaran berhasil dihapus!');
    }
}
