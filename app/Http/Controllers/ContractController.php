<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\VendorContract;
use App\Models\Vendor;
use App\Models\DropdownOption;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        $wedding = Wedding::current();
        $contracts = $wedding->vendorContracts()->with('vendor')->latest()->get();
        $vendors = $wedding->vendors;

        $statuses = DropdownOption::getOptions('contract_status', ['Draft', 'Signed', 'Waiting', 'Completed', 'Cancelled']);

        return view('contracts.index', compact('wedding', 'contracts', 'vendors', 'statuses'));
    }

    public function store(Request $request)
    {
        $wedding = Wedding::current();
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'contract_number' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'dp_amount' => 'required|numeric|min:0',
            'final_amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $wedding->vendorContracts()->create($validated);

        return redirect()->route('contracts.index')->with('success', 'Kontrak vendor berhasil dibuat!');
    }

    public function update(Request $request, VendorContract $contract)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'contract_number' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'dp_amount' => 'required|numeric|min:0',
            'final_amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $contract->update($validated);

        return redirect()->route('contracts.index')->with('success', 'Kontrak vendor berhasil diperbarui!');
    }

    public function destroy(VendorContract $contract)
    {
        $contract->delete();
        return redirect()->route('contracts.index')->with('success', 'Kontrak vendor dihapus!');
    }
}
