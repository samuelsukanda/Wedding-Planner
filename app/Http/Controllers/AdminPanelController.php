<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\DropdownOption;
use Illuminate\Http\Request;

class AdminPanelController extends Controller
{
    public function index(Request $request)
    {
        $wedding = Wedding::first();

        // If no wedding record exists, create a default one
        if (!$wedding) {
            $wedding = Wedding::create([
                'title' => 'Pernikahan Romeo & Juliet',
                'bride_name' => 'Juliet Capulet',
                'groom_name' => 'Romeo Montague',
                'wedding_date' => now()->addMonths(6),
                'total_budget' => 150000000,
                'location' => 'Grand Ballroom Hotel Indonesia, Jakarta',
                'notes' => 'Tema: Modern Minimalist & Botanical Elegance',
            ]);
        }

        $groups = DropdownOption::getGroups();
        $selectedGroup = $request->query('group', 'checklist_category');
        $activeTab = $request->query('tab', 'wedding');

        if (!array_key_exists($selectedGroup, $groups)) {
            $selectedGroup = 'checklist_category';
        }

        $options = DropdownOption::where('group_key', $selectedGroup)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Also get counts for each group badge
        $groupCounts = DropdownOption::selectRaw('group_key, count(*) as total')
            ->groupBy('group_key')
            ->pluck('total', 'group_key')
            ->toArray();

        return view('admin.index', compact(
            'wedding',
            'groups',
            'selectedGroup',
            'activeTab',
            'options',
            'groupCounts'
        ));
    }

    public function updateWedding(Request $request, Wedding $wedding)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'bride_name' => 'required|string|max:255',
            'groom_name' => 'required|string|max:255',
            'wedding_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'total_budget' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $wedding->update($validated);

        return redirect()->route('admin.index', ['tab' => 'wedding'])
            ->with('success', 'Informasi acara pernikahan berhasil diperbarui!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_key' => 'required|string',
            'option_value' => 'required|string|max:255',
            'meta_value' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $groups = DropdownOption::getGroups();
        $groupName = $groups[$request->group_key] ?? 'Master Option';

        DropdownOption::create([
            'group_key' => $validated['group_key'],
            'group_name' => $groupName,
            'option_value' => trim($validated['option_value']),
            'meta_value' => $validated['meta_value'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.index', ['tab' => 'dropdowns', 'group' => $validated['group_key']])
            ->with('success', 'Pilihan dropdown berhasil ditambahkan!');
    }

    public function update(Request $request, DropdownOption $dropdownOption)
    {
        $validated = $request->validate([
            'option_value' => 'required|string|max:255',
            'meta_value' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $dropdownOption->update([
            'option_value' => trim($validated['option_value']),
            'meta_value' => $validated['meta_value'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.index', ['tab' => 'dropdowns', 'group' => $dropdownOption->group_key])
            ->with('success', 'Pilihan dropdown berhasil diperbarui!');
    }

    public function destroy(DropdownOption $dropdownOption)
    {
        $groupKey = $dropdownOption->group_key;
        $dropdownOption->delete();

        return redirect()->route('admin.index', ['tab' => 'dropdowns', 'group' => $groupKey])
            ->with('success', 'Pilihan dropdown berhasil dihapus!');
    }
}
