<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Setelan paket Pro yang tampil di halaman upgrade: harga, rekening tujuan,
 * dan nomor WA admin. Superadmin mengubahnya sendiri lewat halaman ini,
 * jadi tidak ada nominal atau rekening yang tertanam di Blade.
 */
class ProSettingsController extends Controller
{
    /** Kunci app_settings yang dikelola dari halaman ini. */
    private const KEYS = ['pro_price', 'pro_price_before', 'pro_bank', 'admin_whatsapp', 'moodboard_free_limit'];

    public function edit()
    {
        return view('admin.pro-settings', [
            'values' => collect(self::KEYS)
                ->mapWithKeys(fn (string $key) => [$key => Setting::get($key, '')])
                ->all(),
        ]);
    }

    public function update(Request $request)
    {
        // Kolom uang dan telepon diformat supaya terbaca manusia ("Rp 55.000",
        // "6281-2345-6789"), jadi angkanya dibersihkan dulu sebelum divalidasi.
        // Tanpa langkah ini validasi integer gagal karena menerima "Rp 55.000".
        foreach (['pro_price', 'pro_price_before', 'admin_whatsapp'] as $key) {
            $request->merge([$key => $this->digitsOnly($request->input($key))]);
        }

        $validated = $request->validate([
            'pro_price' => ['required', 'integer', 'min:0'],
            // Opsional: harga coret untuk diskon. Kosong = tidak ada coretan.
            'pro_price_before' => ['nullable', 'integer', 'min:0'],
            'pro_bank' => ['required', 'string', 'max:500'],
            'admin_whatsapp' => ['required', 'string', 'max:30'],
            'moodboard_free_limit' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::put($key, (string) $value);
        }

        return redirect()->route('admin.pro-settings.edit')
            ->with('success', 'Pengaturan Pro disimpan.');
    }

    /** Buang semua karakter selain angka. Kosong jadi null supaya aturan nullable tetap berlaku. */
    private function digitsOnly(mixed $value): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        return $digits === '' ? null : $digits;
    }
}