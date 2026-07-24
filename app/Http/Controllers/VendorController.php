<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\Vendor;
use App\Models\VendorPackageItem;
use App\Models\DropdownOption;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $wedding = Wedding::first();
        $query = $wedding->vendors();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('booking_status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $vendors = $query->with('packageItems')->latest()->get();

        $categories = DropdownOption::getOptions('vendor_category', [
            'Venue', 'Catering', 'Dekorasi', 'Wedding Organizer', 'MUA',
            'Fotografer', 'Videografer', 'Hiburan', 'MC', 'Lighting',
            'Sound System', 'Mobil', 'Souvenir', 'Percetakan', 'Wedding Cake',
            'Busana', 'Perhiasan'
        ]);

        $statuses = DropdownOption::getOptions('vendor_status', ['Not Contacted', 'Negotiating', 'Booked', 'Completed', 'Cancelled']);

        return view('vendors.index', compact('wedding', 'vendors', 'categories', 'statuses'));
    }

    public function store(Request $request)
    {
        $wedding = Wedding::first();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'contact' => 'nullable|string',
            'address' => 'nullable|string',
            'google_maps_url' => 'nullable|url',
            'package' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'rating' => 'nullable|numeric|between:0,5',
            'review' => 'nullable|string',
            'booking_status' => 'required|string',
        ]);

        $wedding->vendors()->create($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor berhasil ditambahkan!');
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'contact' => 'nullable|string',
            'address' => 'nullable|string',
            'google_maps_url' => 'nullable|url',
            'package' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'rating' => 'nullable|numeric|between:0,5',
            'review' => 'nullable|string',
            'booking_status' => 'required|string',
        ]);

        $vendor->update($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor berhasil diperbarui!');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('vendors.index')->with('success', 'Vendor berhasil dihapus!');
    }

    public function storePackageItem(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string',
        ]);

        $vendor->packageItems()->create([
            'name' => $validated['name'],
            'type' => $validated['type'] ?? 'Include',
        ]);

        return redirect()->route('vendors.index')->with('success', 'Rincian paketan vendor berhasil ditambahkan!');
    }

    public function destroyPackageItem(VendorPackageItem $item)
    {
        $item->delete();
        return redirect()->route('vendors.index')->with('success', 'Item paketan berhasil dihapus!');
    }
}
