<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorCatalog;
use App\Models\VendorCatalogReview;
use App\Models\Wedding;
use Illuminate\Http\Request;

/**
 * Menu "Cari Vendor": katalog vendor publik hasil import Weddingku.
 *
 * Katalog ini milik bersama (tabel vendor_catalog, tanpa wedding_id),
 * sedangkan hasil "Simpan" masuk ke tabel vendors milik pasangan.
 */
class VendorDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $vendors = VendorCatalog::query()
            // Pencarian & filter di server: katalog ini bisa ratusan baris
            // dan berubah setiap kali import, jadi filter client-side tableFilter
            // tidak dipakai di sini.
            ->when($request->filled('q'), function ($query) use ($request) {
                $needle = trim($request->input('q'));
                $query->where(function ($inner) use ($needle) {
                    $inner->where('name', 'like', "%{$needle}%")
                        ->orWhere('city', 'like', "%{$needle}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->input('category')))
            ->when($request->filled('city'), fn ($query) => $query->where('city', $request->input('city')))
            ->when($request->input('sort') === 'rating', fn ($query) => $query->orderByDesc('rating'), fn ($query) => $query->orderBy('name'))
            ->paginate(24)
            ->withQueryString();

        return view('vendor-directory.index', [
            'vendors' => $vendors,
            'categories' => VendorCatalog::facets('category'),
            'cities' => VendorCatalog::facets('city'),
            'q' => $request->input('q', ''),
            'category' => $request->input('category', ''),
            'city' => $request->input('city', ''),
            'sort' => $request->input('sort', ''),
        ]);
    }

    public function show(VendorCatalog $catalog)
    {
        $wedding = Wedding::current();

        $userReviews = $catalog->userReviews()->with('wedding')->latest()->get();
        $myReview = $wedding
            ? $userReviews->firstWhere('wedding_id', $wedding->id)
            : null;

        return view('vendor-directory.show', [
            'vendor' => $catalog,
            'rooms' => $catalog->venue_rooms ?? [],
            'album' => $catalog->album ?? [],
            // Foto yang tampil di grid; sisanya hanya lewat lightbox.
            'albumPreview' => 6,
            'reviews' => $catalog->reviews ?? [],
            'userReviews' => $userReviews,
            'userRatingAvg' => VendorCatalogReview::averageFor($catalog),
            'myReview' => $myReview,
            'alreadySaved' => Vendor::where('name', $catalog->name)->exists(),
        ]);
    }

    /**
     * Review & rating pasangan untuk vendor katalog. Satu wedding cukup satu
     * review per vendor: mengisi ulang artinya memperbarui.
     */
    public function storeReview(Request $request, VendorCatalog $catalog)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        Wedding::current()->catalogReviews()->updateOrCreate(
            ['vendor_catalog_id' => $catalog->id],
            $validated
        );

        return redirect()
            ->route('vendor-directory.show', $catalog)
            ->with('success', 'Review kamu tersimpan. Terima kasih sudah berbagi pengalaman!');
    }

    /**
     * Salin vendor dari katalog ke Vendor Management milik pasangan.
     * Idempoten: nama yang sama tidak didobelkan.
     */
    public function store(Request $request, VendorCatalog $catalog)
    {
        if (Vendor::where('name', $catalog->name)->exists()) {
            return redirect()
                ->route('vendors.index')
                ->with('error', "\"{$catalog->name}\" sudah ada di Vendor Management kamu.");
        }

        // Deskripsi katalog disimpan utuh; pembatasan karakter hanya ada di
        // tampilan kartu di index (Str::limit di blade), bukan saat menyimpan.
        $vendor = Wedding::current()->vendors()->create([
            'name' => $catalog->name,
            'category' => $catalog->category,
            'contact' => $catalog->phone,
            'address' => $catalog->address,
            'instagram' => $catalog->instagram,
            'whatsapp' => $catalog->whatsapp,
            'rating' => $catalog->rating > 0 ? $catalog->rating : null,
            'review' => $catalog->about ?: null,
            'photo' => $catalog->photo,
            'price' => $catalog->starting_price ?? 0,
        ]);

        // Rincian ruangan ikut dibawa, kalau tidak maka panel "Detail & Isi
        // Paketan" di Vendor Management selalu kosong padahal katalog punya
        // pricelist.
        foreach ($catalog->venue_rooms ?? [] as $room) {
            $vendor->packageItems()->create([
                'name' => $room['name'] ?? 'Ruangan',
                'type' => $this->describeRoom($room),
            ]);
        }

        return redirect()
            ->route('vendors.index')
            ->with('success', "\"{$catalog->name}\" disimpan ke Vendor Management.");
    }

    /**
     * Ringkasan satu ruangan untuk kolom "type" di detail paket: kapasitas
     * lalu harga di baris berikutnya (pemisah newline, dirender dengan
     * whitespace-pre-line).
     */
    private function describeRoom(array $room): ?string
    {
        $parts = [];

        if (! empty($room['capacity'])) {
            $parts[] = $room['capacity'] . ' pax';
        }

        if (! empty($room['price'])) {
            $parts[] = 'Rp ' . number_format($room['price'], 0, ',', '.');
        }

        return $parts === [] ? null : implode("\n", $parts);
    }
}
