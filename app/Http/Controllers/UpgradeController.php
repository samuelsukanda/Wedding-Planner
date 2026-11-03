<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Wedding;
use App\Mail\PurchaseSubmittedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Checkout paket Pro lewat transfer manual.
 *
 * Tidak ada payment gateway, jadi alurnya: user transfer ke rekening yang
 * dicantumkan superadmin, lalu mengunggah bukti. Aktivasi tetap dilakukan
 * superadmin dari daftar request.
 */
class UpgradeController extends Controller
{
    public function index(Request $request)
    {
        $wedding = Wedding::current();
        $isPhotobooth = $request->query('feature') === 'photobooth';

        // Sudah Pro tidak perlu halaman upsell.
        if (! $isPhotobooth && $wedding?->isPro()) {
            return redirect()->route('admin.index')
                ->with('success', 'Wedding Kit ini sudah Pro. Semua fitur Pro terbuka.');
        }

        return view('upgrade.index', [
            'wedding' => $wedding,
            'pending' => $wedding?->upgradeRequests()
                ->where('feature', $isPhotobooth ? 'photobooth' : 'pro')
                ->where('status', 'pending')->latest()->first(),
            'rejected' => $wedding?->upgradeRequests()
                ->where('feature', $isPhotobooth ? 'photobooth' : 'pro')
                ->where('status', 'rejected')->latest()->first(),
            'price' => (int) Setting::get($isPhotobooth ? 'photobooth_price' : 'pro_price', 0),
            'priceBefore' => $isPhotobooth ? 0 : (int) Setting::get('pro_price_before', 0),
            'isPhotobooth' => $isPhotobooth,
            'banks' => Setting::proBanks(),
            // Narasi singkat yang ditulis superadmin, tampil di atas
            // rekening/QRIS sebagai petunjuk pembayaran.
            'paymentNote' => Setting::get('pro_payment_note', ''),
            'qrisPath' => Setting::get('pro_qris_path', ''),
        ]);
    }

    public function store(Request $request)
    {
        $wedding = Wedding::current();
        $isPhotobooth = $request->input('feature') === 'photobooth';

        abort_if(! $wedding, 403, 'Akun belum terhubung ke acara pernikahan.');

        // Satu request yang menunggu konfirmasi saja. Tanpa ini user bisa
        // menumpuk bukti transfer dan antrean admin jadi tidak terbaca.
        $validated = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $created = DB::transaction(function () use ($wedding, $request, $validated, $isPhotobooth) {
            $wedding = $wedding->newQuery()->lockForUpdate()->findOrFail($wedding->id);
            if ($wedding->upgradeRequests()
                ->where('feature', $isPhotobooth ? 'photobooth' : 'pro')
                ->where('status', 'pending')->exists()) return false;
            $wedding->upgradeRequests()->create([
            'user_id' => $request->user()->id,
            'feature' => $isPhotobooth ? 'photobooth' : 'pro',
            'plan' => $isPhotobooth ? $wedding->plan : 'pro',
            // Harga direkam saat request dibuat supaya riwayat audit tetap
            // konsisten bila nanti superadmin menaikkan harga.
            'amount' => (int) Setting::get($isPhotobooth ? 'photobooth_price' : 'pro_price', 0),
            'proof_path' => $validated['proof']->store('upgrade-proofs', 'local'),
            'status' => 'pending',
            ]);
            return true;
        });
        if (! $created) return back()->with('error', 'Kamu sudah mengirim bukti transfer dan masih menunggu konfirmasi admin.');

        $this->notifyPurchase($wedding, $isPhotobooth, (int) Setting::get($isPhotobooth ? 'photobooth_price' : 'pro_price', 0), $request->user()->id);

        return redirect()->route('upgrade.index', $isPhotobooth ? ['feature' => 'photobooth'] : [])
            ->with('success', 'Bukti transfer terkirim. Menunggu konfirmasi admin.');
    }

    /**
     * Kirim email notifikasi ke superadmin tentang pembelian baru.
     */
    protected function notifyPurchase(\App\Models\Wedding $wedding, bool $isPhotobooth, int $amount, int $userId): void
    {
        $recipients = \App\Models\User::where('is_superadmin', true)->pluck('email')->filter()->all();
        if (empty($recipients)) {
            $recipients = [config('mail.from.address', 'nupsera@gmail.com')];
        }

        try {
            \Illuminate\Support\Facades\Mail::to($recipients)->send(new \App\Mail\PurchaseSubmittedMail(
                $isPhotobooth ? 'photobooth' : 'pro',
                (string) $wedding->couple_name,
                $amount,
                $userId
            ));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
