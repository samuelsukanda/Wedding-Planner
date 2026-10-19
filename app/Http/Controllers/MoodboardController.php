<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\Moodboard;
use App\Models\DropdownOption;
use Illuminate\Http\Request;

class MoodboardController extends Controller
{
    /**
     * Copy halaman terkunci, dipisah dari view supaya mudah dibaca dan diuji.
     */
    private const LOCKED_COPY = [
        'title' => 'Moodboard',
        'description' => 'Kumpulan inspirasi wedding kamu — dekor, gaun, bouquet, venue — dalam board yang bisa dibagi ke pasangan.',
        'highlights' => ['Sage', 'Bouquet', 'Akad', 'Rustic'],
        'note' => 'Buka semua Wedding Kit + Invite Pasangan + Multi-device',
    ];

    public function index(Request $request)
    {
        $wedding = Wedding::current();

        // Moodboard dikunci penuh, bukan berbasis kuota: untuk paket Free daftar
        // item-nya tidak pernah dirender, hanya halaman penjelasan yang tampil.
        if (! $wedding->hasFeature('moodboard')) {
            return view('moodboards.locked', ['feature' => self::LOCKED_COPY]);
        }

        $query = $wedding->moodboards();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $moodboards = $query->latest()->get();

        $categories = DropdownOption::getOptions('moodboard_category', ['Dekorasi', 'Pelaminan', 'Gaun Pengantin', 'Suit', 'Makeup', 'Undangan', 'Souvenir', 'Table Setting']);

        return view('moodboards.index', [
            'wedding' => $wedding,
            'moodboards' => $moodboards,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $wedding = Wedding::current();

        if ($this->locked($wedding)) {
            return redirect()->route('moodboards.index');
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
        // Item sisa dari pasangan yang sebelumnya masih Free tetap bisa ada,
        // jadi jalur edit ikut tertutup - bukan hanya halaman daftarnya.
        if ($this->locked(Wedding::current())) {
            return redirect()->route('moodboards.index');
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
        if ($this->locked(Wedding::current())) {
            return redirect()->route('moodboards.index');
        }

        $moodboard->delete();
        return redirect()->route('moodboards.index')->with('success', 'Moodboard berhasil dihapus!');
    }

    /** Moodboard hanya untuk paket Pro. Satu-satunya sumber kebenarannya Wedding::hasFeature(). */
    private function locked(Wedding $wedding): bool
    {
        return ! $wedding->hasFeature('moodboard');
    }
}
