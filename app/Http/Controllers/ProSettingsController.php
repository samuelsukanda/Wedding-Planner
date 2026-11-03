<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Setelan harga paket Pro. Rekening/QRIS/deskripsi dipindah ke RekeningController.
 */
class ProSettingsController extends Controller
{
    /** Kunci app_settings yang dikelola dari halaman ini. */
    private const KEYS = ['pro_price', 'pro_price_before'];

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
        foreach (self::KEYS as $key) {
            $request->merge([$key => $this->digitsOnly($request->input($key))]);
        }

        $validated = $request->validate([
            'pro_price' => ['required', 'integer', 'min:0'],
            'pro_price_before' => ['nullable', 'integer', 'min:0'],
        ]);

        Setting::put('pro_price', (string) $validated['pro_price']);
        Setting::put('pro_price_before', (string) ($validated['pro_price_before'] ?? ''));

        return redirect()->route('admin.pro-settings.index')
            ->with('success', 'Pengaturan Pro disimpan.');
    }

    /** Buang semua karakter selain angka. Kosong jadi null. */
    private function digitsOnly(mixed $value): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $value);
        return $digits === '' ? null : $digits;
    }
}
