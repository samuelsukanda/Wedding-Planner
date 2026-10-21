<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Sudah Pro tidak perlu halaman upsell.
        if ($wedding?->isPro()) {
            return redirect()->route('admin.index')
                ->with('success', 'Wedding Kit ini sudah Pro. Semua fitur Pro terbuka.');
        }

        return view('upgrade.index', [
            'wedding' => $wedding,
            'pending' => $wedding?->upgradeRequests()->where('status', 'pending')->latest()->first(),
            'rejected' => $wedding?->upgradeRequests()->where('status', 'rejected')->latest()->first(),
            'price' => (int) Setting::get('pro_price', 0),
            'priceBefore' => (int) Setting::get('pro_price_before', 0),
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

        abort_if(! $wedding, 403, 'Akun belum terhubung ke acara pernikahan.');

        // Satu request yang menunggu konfirmasi saja. Tanpa ini user bisa
        // menumpuk bukti transfer dan antrean admin jadi tidak terbaca.
        $validated = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $created = DB::transaction(function () use ($wedding, $request, $validated) {
            $wedding = $wedding->newQuery()->lockForUpdate()->findOrFail($wedding->id);
            if ($wedding->upgradeRequests()->where('status', 'pending')->exists()) return false;
            $wedding->upgradeRequests()->create([
            'user_id' => $request->user()->id,
            'plan' => 'pro',
            // Harga direkam saat request dibuat supaya riwayat audit tetap
            // konsisten bila nanti superadmin menaikkan harga.
            'amount' => (int) Setting::get('pro_price', 0),
            'proof_path' => $validated['proof']->store('upgrade-proofs', 'local'),
            'status' => 'pending',
            ]);
            return true;
        });
        if (! $created) return back()->with('error', 'Kamu sudah mengirim bukti transfer dan masih menunggu konfirmasi admin.');

        return redirect()->route('upgrade.index')
            ->with('success', 'Bukti transfer terkirim. Menunggu konfirmasi admin.');
    }
}
