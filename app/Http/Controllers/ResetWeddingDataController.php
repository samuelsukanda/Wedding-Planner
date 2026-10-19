<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Hapus semua worksheet satu Wedding Kit.
 *
 * Sengaja tidak memakai Eloquent: tabel dikosongkan langsung per wedding_id
 * di dalam satu transaksi, karena daftar tabelnya diambil dari skema database
 * dan sebagian tabel tidak punya relasi Eloquent ke Wedding.
 */
class ResetWeddingDataController extends Controller
{
    /**
     * Tabel yang dikosongkan, anak lebih dulu dari induknya.
     *
     * Daftar ini hasil audit information_schema terhadap kolom wedding_id,
     * bukan daftar asumsi: semua tabel di database yang punya wedding_id
     * tercatat di sini. Tabel tanpa wedding_id tidak disentuh - vendor_catalog
     * milik semua pasangan, dan vendor_package_items ikut hilang lewat cascade
     * dari vendors.
     */
    private const WIPE = [
        'wedding_invites',
        'savings_transactions',
        'savings_goals',
        'document_histories',
        'document_requirements',
        'proposal_checklists',
        'proposal_guests',
        'proposal_budgets',
        'proposal_events',
        'vendor_contracts',
        'vendor_payments',
        'vendors',
        'budgets',
        'checklists',
        'gifts',
        'guests',
        'moodboards',
        'notifications',
        'reports',
        'rundown_events',
        'souvenirs',
        'vendor_catalog_reviews',
    ];

    public function destroy(Request $request)
    {
        $wedding = Wedding::current();

        abort_if(! $wedding, 403, 'Akun belum terhubung ke acara pernikahan.');

        // Kata kunci wajib supaya reset tidak terjadi karena klik yang tidak
        // sengaja. Dicek di server, bukan hanya disables tombolnya.
        $request->validate([
            'confirm' => ['required', 'in:RESET'],
        ]);

        DB::transaction(function () use ($wedding) {
            foreach (self::WIPE as $table) {
                DB::table($table)->where('wedding_id', $wedding->id)->delete();
            }

            // Profil pernikahan ikut dikosongkan, tapi plan (status Pro) tetap
            // utuh: paket yang sudah dibeli tidak hilang karena reset.
            $wedding->update([
                'bride_name' => null,
                'groom_name' => null,
                'wedding_date' => null,
                'total_budget' => 0,
                'location' => null,
                'notes' => null,
                'wa_template' => null,
            ]);
        });

        return redirect()->route('admin.index')
            ->with('success', 'Semua worksheet dan profil pernikahan berhasil dihapus.');
    }
}