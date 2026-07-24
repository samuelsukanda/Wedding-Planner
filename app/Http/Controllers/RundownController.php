<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\RundownEvent;
use Illuminate\Http\Request;

class RundownController extends Controller
{
    public function index()
    {
        $wedding = Wedding::first();
        $rundowns = $wedding->rundownEvents()->orderBy('sort_order', 'asc')->get();

        return view('rundowns.index', compact('wedding', 'rundowns'));
    }

    public function store(Request $request)
    {
        $wedding = Wedding::first();
        $validated = $request->validate([
            'time' => 'required|string',
            'activity' => 'required|string|max:255',
            'pic' => 'required|string|max:255',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $wedding->rundownEvents()->create($validated);

        return redirect()->route('rundowns.index')->with('success', 'Jadwal acara berhasil ditambahkan!');
    }

    public function update(Request $request, RundownEvent $rundown)
    {
        $validated = $request->validate([
            'time' => 'required|string',
            'activity' => 'required|string|max:255',
            'pic' => 'required|string|max:255',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $rundown->update($validated);

        return redirect()->route('rundowns.index')->with('success', 'Jadwal acara diperbarui!');
    }

    public function destroy(RundownEvent $rundown)
    {
        $rundown->delete();
        return redirect()->route('rundowns.index')->with('success', 'Jadwal acara dihapus!');
    }
}
