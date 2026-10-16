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
        $wedding = Wedding::current();
        $query = $wedding->moodboards();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $moodboards = $query->latest()->get();

        $categories = DropdownOption::getOptions('moodboard_category', ['Dekorasi', 'Pelaminan', 'Gaun Pengantin', 'Suit', 'Makeup', 'Undangan', 'Souvenir', 'Table Setting']);

        // Dipakai view untuk menampilkan sisa kuota dan menjadikan tombol
        // "Tambah" jadi link upgrade kalau sudah penuh. Filter kategori tidak
        // ikut dihitung — kuota berlaku untuk seluruh item moodboard.
        return view('moodboards.index', [
            'wedding' => $wedding,
            'moodboards' => $moodboards,
            'categories' => $categories,
            'limit' => $wedding->limitFor('moodboard'),
            'used' => $wedding->moodboards()->count(),
        ]);
    }

    public function store(Request $request)
    {
        $wedding = Wedding::current();

        // Kuota dicek sebelum validasi: kalau sudah penuh, tidak ada gunanya
        // memvalidasi form yang pasti ditolak. Ini satu-satunya jalur tulis
        // item baru, jadi cukup di sini.
        $limit = $wedding->limitFor('moodboard');

        if ($limit !== null && $wedding->moodboards()->count() >= $limit) {
            return back()->with('error', "Paket Free bisa menyimpan maksimal {$limit} item moodboard. Upgrade ke Pro untuk menambah tanpa batas.");
        }

        $validated = $request->validate([
            'category' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link_reference' => 'nullable|url',
            'notes' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $validated['link_preview'] = $this->fetchOGImage($validated['link_reference'] ?? null);

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

        $validated['link_preview'] = $this->fetchOGImage($validated['link_reference'] ?? null);

        $moodboard->update($validated);

        return redirect()->route('moodboards.index')->with('success', 'Inspirasi Moodboard berhasil diperbarui!');
    }

    private function fetchOGImage(?string $url): ?string
    {
        if (!$url) return null;

        try {
            $html = @file_get_contents($url, false, stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]));

            if (!$html) return null;

            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML($html);
            libxml_clear_errors();

            $metas = $dom->getElementsByTagName('meta');
            foreach ($metas as $meta) {
                $property = $meta->getAttribute('property');
                $name = $meta->getAttribute('name');
                if (strtolower($property) === 'og:image' || strtolower($name) === 'twitter:image') {
                    $content = $meta->getAttribute('content');
                    if ($content) return $content;
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    public function destroy(Moodboard $moodboard)
    {
        $moodboard->delete();
        return redirect()->route('moodboards.index')->with('success', 'Moodboard berhasil dihapus!');
    }
}
