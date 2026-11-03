<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Setelan rekening tujuan transfer + QRIS + deskripsi pembayaran yang tampil
 * pada halaman upgrade. Dipisah dari Pengaturan Pro (harga) supaya menu
 * rekening berdiri sendiri.
 */
class RekeningController extends Controller
{
    /** Kolom teks panjang bebas: narasi singkat di atas rekening tujuan. */
    private const TEXT_KEYS = ['pro_payment_note'];

    /** Kunci path foto QRIS di disk public. */
    private const QRIS_KEY = 'pro_qris_path';

    /**
     * Ikon yang boleh dipakai tiap rekening.
     *
     * @var array<string, string>
     */
    private const BANK_ICONS = [
        'fa-building-columns' => 'Gedung Bank',
        'fa-landmark' => 'Landmark',
        'fa-money-bill-transfer' => 'Transfer',
        'fa-money-bill-wave' => 'Uang Tunai',
        'fa-wallet' => 'Dompet',
        'fa-credit-card' => 'Kartu',
        'fa-mobile-screen-button' => 'E-Wallet / Mobile',
        'fa-vault' => 'Brankas',
        'fa-piggy-bank' => 'Tabungan',
    ];

    public function edit()
    {
        return view('admin.rekening', [
            'values' => collect(self::TEXT_KEYS)
                ->mapWithKeys(fn (string $key) => [$key => Setting::get($key, '')])
                ->all(),
            'banks' => Setting::proBanks(),
            'qrisPath' => Setting::get(self::QRIS_KEY, ''),
            'icons' => collect(self::BANK_ICONS)
                ->map(fn (string $label, string $icon) => ['value' => $icon, 'label' => $label])
                ->values()
                ->all(),
        ]);
    }

    public function update(Request $request)
    {
        $request->merge([
            'banks' => array_values(array_filter(
                (array) $request->input('banks', []),
                fn ($row) => is_array($row) && trim((string) ($row['number'] ?? '')) !== ''
            )),
        ]);

        $validated = $request->validate([
            'banks' => ['required', 'array', 'min:1'],
            'banks.*.name' => ['required', 'string', 'max:40'],
            'banks.*.number' => ['required', 'string', 'max:60'],
            'banks.*.icon' => ['required', Rule::in(array_keys(self::BANK_ICONS))],
            'pro_payment_note' => ['nullable', 'string', 'max:300'],
            'pro_qris' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'banks.required' => 'Isi minimal satu rekening tujuan.',
            'banks.min' => 'Isi minimal satu rekening tujuan.',
            'banks.*.name.required' => 'Nama bank wajib diisi.',
            'banks.*.number.required' => 'Nomor rekening wajib diisi.',
        ]);

        Setting::put('pro_banks', json_encode(
            array_map(fn (array $row) => [
                'icon' => $row['icon'],
                'name' => trim($row['name']),
                'number' => trim($row['number']),
            ], $validated['banks']),
            JSON_UNESCAPED_UNICODE
        ));

        if ($request->hasFile('pro_qris')) {
            $oldQris = Setting::get(self::QRIS_KEY, '');

            if ($oldQris && Storage::disk('public')->exists($oldQris)) {
                Storage::disk('public')->delete($oldQris);
            }

            Setting::put(self::QRIS_KEY, $request->file('pro_qris')->store('qris', 'public'));
        }

        foreach (self::TEXT_KEYS as $key) {
            Setting::put($key, trim((string) ($validated[$key] ?? '')));
        }

        return redirect()->route('admin.rekening.index')
            ->with('success', 'Rekening pembayaran disimpan.');
    }
}
