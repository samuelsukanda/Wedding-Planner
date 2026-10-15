<?php

namespace App\Http\Controllers;

use App\Models\UpgradeRequest;
use Illuminate\Http\Request;

/**
 * Antrean persetujuan upgrade Pro. Semua route di controller ini ada di dalam
 * middleware 'superadmin', jadi gate aksesnya sudah ditangani di routes/web.php.
 */
class AdminUpgradeController extends Controller
{
    public function index()
    {
        // Pending didahulukan supaya request yang menunggu action selalu di atas.
        $requests = UpgradeRequest::with(['wedding', 'user', 'reviewer'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->latest()
            ->get();

        return view('admin.upgrade-requests', ['requests' => $requests]);
    }

    public function approve(UpgradeRequest $upgradeRequest)
    {
        $wedding = $upgradeRequest->wedding;

        $wedding->update(['plan' => $upgradeRequest->plan]);

        $upgradeRequest->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $wedding->notifications()->create([
            'title' => 'Upgrade Pro Disetujui',
            'message' => 'Wedding Kit kamu sudah aktif Pro. Semua fitur Pro sekarang terbuka, tanpa batas waktu.',
            'type' => 'info',
        ]);

        return back()->with('success', 'Pro diaktifkan untuk ' . $wedding->couple_name . '.');
    }

    public function reject(Request $request, UpgradeRequest $upgradeRequest)
    {
        // Catatan wajib: pasangan harus tahu alasannya supaya bisa transfer ulang.
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ]);

        $upgradeRequest->update($validated + [
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Request ditolak. Pasangan bisa mengirim ulang bukti transfer.');
    }
}