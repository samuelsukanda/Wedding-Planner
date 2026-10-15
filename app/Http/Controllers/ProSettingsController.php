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
    private const KEYS = ['pro_price', 'pro_bank', 'admin_whatsapp'];

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
        $validated = $request->validate([
            'pro_price' => ['required', 'integer', 'min:0'],
            'pro_bank' => ['required', 'string', 'max:500'],
            'admin_whatsapp' => ['required', 'string', 'max:30'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::put($key, (string) $value);
        }

        return redirect()->route('admin.pro-settings.edit')
            ->with('success', 'Pengaturan Pro disimpan.');
    }
}