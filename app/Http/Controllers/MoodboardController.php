<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\Moodboard;
use App\Models\DropdownOption;
use Illuminate\Http\Request;

class MoodboardController extends Controller
{
    public function index(Request $request)
    {
        $wedding = Wedding::first();
        $query = $wedding->moodboards();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $moodboards = $query->latest()->get();

        $categories = DropdownOption::getOptions('moodboard_category', ['Dekorasi', 'Pelaminan', 'Gaun Pengantin', 'Suit', 'Makeup', 'Undangan', 'Souvenir', 'Table Setting']);

        return view('moodboards.index', compact('wedding', 'moodboards', 'categories'));
    }

    public function store(Request $request)
    {
        $wedding = Wedding::first();
        $validated = $request->validate([
            'category' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link_reference' => 'nullable|url',
            'notes' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $wedding->moodboards()->create($validated);

        return redirect()->route('moodboards.index')->with('success', 'Inspirasi Moodboard berhasil ditambahkan!');
    }

    public function update(Request $request, Moodboard $moodboard)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link_reference' => 'nullable|url',
            'notes' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $moodboard->update($validated);

        return redirect()->route('moodboards.index')->with('success', 'Inspirasi Moodboard berhasil diperbarui!');
    }

    public function destroy(Moodboard $moodboard)
    {
        $moodboard->delete();
        return redirect()->route('moodboards.index')->with('success', 'Moodboard berhasil dihapus!');
    }
}
