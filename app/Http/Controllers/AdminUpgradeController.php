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
        // Verifikasi status dulu (tanpa kunci) biar tidak buang waktu kerja
        // eksternal kalau sudah diproses.
        abort_if($upgradeRequest->status !== 'pending', 409, 'Request upgrade sudah diproses.');

        // Provisioning photobooth = panggilan HTTP eksternal; dijalankan DI LUAR
        // DB::transaction supaya tidak menahan kunci baris selama network I/O
        // (timeout 10s), dan kalau tulis DB gagal, event remote tidak menjadi yatim.
        $photo = null;
        if ($upgradeRequest->feature === 'photobooth') {
            $photo = $this->provisionPhotobooth($upgradeRequest->wedding);
        }

        $wedding = DB::transaction(function () use ($upgradeRequest, $photo) {
            $upgradeRequest = UpgradeRequest::query()->lockForUpdate()->findOrFail($upgradeRequest->id);
            abort_if($upgradeRequest->status !== 'pending', 409, 'Request upgrade sudah diproses.');
            $wedding = $upgradeRequest->wedding()->lockForUpdate()->firstOrFail();

            if ($upgradeRequest->feature === 'photobooth') {
                $wedding->update([
                    'photobooth_event_slug' => $photo['slug'],
                    'photobooth_api_key' => $photo['api_key'],
                    'photobooth_active' => true,
                ]);
                $title = 'Photobooth Disetujui';
                $message = 'Photobooth kamu sudah aktif. QR dan pengaturannya sudah siap dipakai.';
            } else {
                $wedding->update(['plan' => $upgradeRequest->plan]);
                $title = 'Upgrade Pro Disetujui';
                $message = 'Wedding Kit kamu sudah aktif Pro. Semua fitur Pro sekarang terbuka, tanpa batas waktu.';
            }

            $upgradeRequest->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $wedding->notifications()->create([
                'title' => $title,
                'message' => $message,
                'type' => 'info',
            ]);

            return $wedding;
        });

        // Setelah commit, api_key sudah tersimpan di wedding — aman panggil
        // updateEvent (eksternal) untuk kuota.
        if ($photo) {
            $quota = (int) \App\Models\Setting::get('photobooth_photo_quota', 300);
            if ($quota > 0) {
                app(\App\Services\PhotoboothApi::class)->updateEvent($wedding, ['photo_quota' => $quota])->throw();
            }
        }

        return back()->with('success', $upgradeRequest->feature === 'photobooth'
            ? 'Photobooth diaktifkan untuk ' . $upgradeRequest->wedding->couple_name . '.'
            : 'Pro diaktifkan untuk ' . $upgradeRequest->wedding->couple_name . '.');
    }

    /**
     * Minta Photobooth membuat (atau memperbarui) event untuk pasangan dan
     * kembalikan slug + api_key. Hanya panggilan HTTP; tidak menulis DB.
     */
    private function provisionPhotobooth(\App\Models\Wedding $wedding): array
    {
        $couple = trim($wedding->groom_name . ' & ' . $wedding->bride_name);

        $payload = [
            'title' => $couple . ' Wedding',
            'groom_name' => $wedding->groom_name,
            'bride_name' => $wedding->bride_name,
            'wedding_date' => $wedding->wedding_date?->format('Y-m-d'),
            'frame_text' => $couple,
            'frame_font' => config('photobooth.default_font'),
        ];

        if ($wedding->photobooth_event_slug) {
            $payload['slug'] = $wedding->photobooth_event_slug;
        }

        try {
            $response = app(\App\Services\PhotoboothApi::class)->provision($payload)->throw();
        } catch (\Throwable $e) {
            report($e);
            abort(409, 'Gagal membuat event photobooth. Pastikan layanan photobooth aktif, lalu coba approve lagi.');
        }

        return [
            'slug' => $response->json('slug'),
            'api_key' => $response->json('api_key'),
        ];
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
