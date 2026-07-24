<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\VendorPayment;
use App\Models\Vendor;
use App\Models\DropdownOption;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $wedding = Wedding::first();
        $payments = $wedding->vendorPayments()->with('vendor')->latest()->get();
        $vendors = $wedding->vendors;

        $statuses = DropdownOption::getOptions('payment_status', ['Belum Bayar', 'DP', 'Cicilan', 'Lunas']);

        return view('payments.index', compact('wedding', 'payments', 'vendors', 'statuses'));
    }

    public function store(Request $request)
    {
        $wedding = Wedding::first();
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'nominal' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string',
            'status' => 'required|string',
            'reminder_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $wedding->vendorPayments()->create($validated);

        return redirect()->route('payments.index')->with('success', 'Catatan pembayaran berhasil ditambahkan!');
    }

    public function update(Request $request, VendorPayment $payment)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'nominal' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string',
            'status' => 'required|string',
            'reminder_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);

        return redirect()->route('payments.index')->with('success', 'Catatan pembayaran berhasil diperbarui!');
    }

    public function destroy(VendorPayment $payment)
    {
        $payment->delete();
        return redirect()->route('payments.index')->with('success', 'Catatan pembayaran dihapus!');
    }
}
