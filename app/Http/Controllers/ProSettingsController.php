<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Setelan paket Pro yang tampil di halaman upgrade: harga dan rekening tujuan.
 * Superadmin mengubahnya sendiri lewat halaman ini, jadi tidak ada nominal atau
 * rekening yang tertanam di Blade.
 */
class ProSettingsController extends Controller
{
    /** Kunci app_settings yang dikelola dari halaman ini. */
    private const KEYS = ['pro_price', 'pro_price_before'];

    /**
     * Kolom teks panjang bebas (trim saja, tanpa validasi angka): narasi
     * singkat yang tampil di atas rekening tujuan pada halaman upgrade.
     */
    private const TEXT_KEYS = ['pro_payment_note'];

    /** Kunci path foto QRIS di disk public. */
    private const QRIS_KEY = 'pro_qris_path';

    /**
     * Ikon yang boleh dipakai tiap rekening. FontAwesome tidak punya logo bank
     * Indonesia, jadi yang dipilih adalah ikon generik yang tetap jelas di
     * ukuran kecil.
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
        return view('admin.pro-settings', [
            'values' => collect(array_merge(self::KEYS, self::TEXT_KEYS))
                ->mapWithKeys(fn (string $key) => [$key => Setting::get($key, '')])
                ->all(),
            'banks' => Setting::proBanks(),
            // Foto QRIS di disk public; kosong berarti belum diunggah.
            'qrisPath' => Setting::get(self::QRIS_KEY, ''),
            // Dikirim sebagai list, bukan map, supaya x-for di Blade membaca
            // pasangan nilai/label tanpa menebak urutan kunci objek.
            'icons' => collect(self::BANK_ICONS)
                ->map(fn (string $label, string $icon) => ['value' => $icon, 'label' => $label])
                ->values()
                ->all(),
        ]);
    }

    public function update(Request $request)
    {
        // Kolom uang diformat supaya terbaca manusia ("Rp 55.000"), jadi
        // angkanya dibersihkan dulu sebelum divalidasi. Tanpa langkah ini
        // validasi integer gagal karena menerima "Rp 55.000".
        foreach (self::KEYS as $key) {
            $request->merge([$key => $this->digitsOnly($request->input($key))]);
        }

        // Baris yang dikosongkan superadmin dibuang sebelum divalidasi, supaya
        // tombol "Tambah Rekening" yang tidak jadi dipakai tidak memblokir
        // penyimpanan.
        $request->merge([
            'banks' => array_values(array_filter(
                (array) $request->input('banks', []),
                fn ($row) => is_array($row) && trim((string) ($row['number'] ?? '')) !== ''
            )),
        ]);

        $validated = $request->validate([
            'pro_price' => ['required', 'integer', 'min:0'],
            // Opsional: harga coret untuk diskon. Kosong = tidak ada coretan.
            'pro_price_before' => ['nullable', 'integer', 'min:0'],
            'banks' => ['required', 'array', 'min:1'],
            'banks.*.name' => ['required', 'string', 'max:40'],
            'banks.*.number' => ['required', 'string', 'max:60'],
            'banks.*.icon' => ['required', Rule::in(array_keys(self::BANK_ICONS))],
            // Narasi singkat di atas rekening tujuan pada halaman upgrade.
            'pro_payment_note' => ['nullable', 'string', 'max:300'],
            // Foto QRIS opsional; mengunggah baru menimpa yang lama.
            'pro_qris' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'banks.required' => 'Isi minimal satu rekening tujuan.',
            'banks.min' => 'Isi minimal satu rekening tujuan.',
            'banks.*.name.required' => 'Nama bank wajib diisi.',
            'banks.*.number.required' => 'Nomor rekening wajib diisi.',
        ]);

        Setting::put('pro_price', (string) $validated['pro_price']);
        Setting::put('pro_price_before', (string) ($validated['pro_price_before'] ?? ''));

        Setting::put('pro_banks', json_encode(
            array_map(fn (array $row) => [
                'icon' => $row['icon'],
                'name' => trim($row['name']),
                'number' => trim($row['number']),
            ], $validated['banks']),
            JSON_UNESCAPED_UNICODE
        ));

        // Foto QRIS: unggah baru menghapus file lamanya supaya disk tidak
        // menumpuk gambar yang sudah tidak terpakai.
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

        return redirect()->route('admin.pro-settings.index')
            ->with('success', 'Pengaturan Pro disimpan.');
    }

    /** Buang semua karakter selain angka. Kosong jadi null supaya aturan nullable tetap berlaku. */
    private function digitsOnly(mixed $value): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        return $digits === '' ? null : $digits;
    }
}
