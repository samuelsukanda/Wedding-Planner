<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorCatalog;
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
        return view('vendor-directory.show', [
            'vendor' => $catalog,
            'alreadySaved' => Vendor::where('name', $catalog->name)->exists(),
        ]);
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

        Wedding::current()->vendors()->create([
            'name' => $catalog->name,
            'category' => $catalog->category,
            'contact' => $catalog->phone,
            'address' => $catalog->address,
            'instagram' => $catalog->instagram,
            'whatsapp' => $catalog->whatsapp,
            'rating' => $catalog->rating > 0 ? $catalog->rating : null,
            'review' => $catalog->about,
            'photo' => $catalog->photo,
        ]);

        return redirect()
            ->route('vendors.index')
            ->with('success', "\"{$catalog->name}\" disimpan ke Vendor Management.");
    }
}
