<?php

namespace App\Http\Controllers;

use App\Models\DropdownOption;
use App\Models\UpgradeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Antrean persetujuan upgrade Pro. Semua route di controller ini ada di dalam
 * middleware 'superadmin', jadi gate aksesnya sudah ditangani di routes/web.php.
 */
class AdminUpgradeController extends Controller
{
    public function proof(UpgradeRequest $upgradeRequest): BinaryFileResponse
    {
        abort_unless($upgradeRequest->proof_path && Storage::disk('local')->exists($upgradeRequest->proof_path), 404);
        return response()->file(Storage::disk('local')->path($upgradeRequest->proof_path));
    }
    /** Alasan bawaan kalau superadmin belum mengisi group Master Data. */
    private const DEFAULT_REJECT_REASONS = [
        'Bukti transfer tidak terbaca, silakan kirim ulang yang lebih jelas',
        'Nominal transfer tidak sesuai dengan harga paket Pro',
        'Transfer berasal dari rekening selain yang kami cantumkan',
        'Dana belum kami terima, mohon konfirmasi ke admin',
    ];

    public function index()
    {
        // Pending didahulukan supaya request yang menunggu action selalu di atas.
        $requests = UpgradeRequest::with(['wedding', 'user', 'reviewer'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->latest()
            ->get();

        return view('admin.upgrade-requests', [
            'requests' => $requests,
            'rejectReasons' => DropdownOption::getOptions('upgrade_reject_reason', self::DEFAULT_REJECT_REASONS),
        ]);
    }

    public function approve(UpgradeRequest $upgradeRequest)
    {
        $wedding = $upgradeRequest->wedding;

        DB::transaction(function () use ($upgradeRequest) {
        $upgradeRequest = UpgradeRequest::query()->lockForUpdate()->findOrFail($upgradeRequest->id);
        abort_if($upgradeRequest->status !== 'pending', 409, 'Request upgrade sudah diproses.');
        $wedding = $upgradeRequest->wedding()->lockForUpdate()->firstOrFail();
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
        });

        return back()->with('success', 'Pro diaktifkan untuk ' . $wedding->couple_name . '.');
    }

    public function reject(Request $request, UpgradeRequest $upgradeRequest)
    {
        // Catatan wajib: pasangan harus tahu alasannya supaya bisa transfer ulang.
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($upgradeRequest, $validated) {
            $upgradeRequest = UpgradeRequest::query()->lockForUpdate()->findOrFail($upgradeRequest->id);
            abort_if($upgradeRequest->status !== 'pending', 409, 'Request upgrade sudah diproses.');
            $upgradeRequest->update($validated + [
                'status' => 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', 'Request ditolak. Pasangan bisa mengirim ulang bukti transfer.');
    }

    public function destroy(UpgradeRequest $upgradeRequest)
    {
        // Bukti transfer ikut dihapus. Kalau dibiarkan, file yang sudah tidak
        // dirujuk siapa pun akan menumpuk di storage.
        if ($upgradeRequest->proof_path) {
            Storage::disk('local')->delete($upgradeRequest->proof_path);
        }

        $upgradeRequest->delete();

        return back()->with('success', 'Request upgrade dihapus.');
    }
}
