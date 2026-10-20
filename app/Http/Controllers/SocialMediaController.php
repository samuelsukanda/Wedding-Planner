<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Kontak sosial "Hubungi Kami" yang tampil di halaman Profile.
 * WhatsApp divalidasi khusus (digit telepon), Instagram & TikTok berupa
 * link profil. Semuanya opsional: key yang kosong tidak dirender.
 */
class SocialMediaController extends Controller
{
    /** Kunci app_settings yang dikelola dari halaman ini. */
    private const KEYS = ['social_whatsapp', 'social_instagram', 'social_tiktok'];

    public function edit()
    {
        return view('admin.social-media', [
            'values' => collect(self::KEYS)
                ->mapWithKeys(fn (string $key) => [$key => Setting::get($key, '')])
                ->all(),
        ]);
    }

    public function update(Request $request)
    {
        // WhatsApp diformat supaya terbaca manusia ("6281-2345-6789"), jadi
        // angkanya dibersihkan dulu sebelum diverifikasi.
        if ($request->filled('social_whatsapp')) {
            $request->merge(['social_whatsapp' => preg_replace('/\D/', '', (string) $request->input('social_whatsapp'))]);
        }

        $validated = $request->validate([
            // Kontak sosial opsional; kosong berarti tombolnya tidak ditampilkan.
            'social_whatsapp' => ['nullable', 'digits_between:8,15'],
            'social_instagram' => ['nullable', 'string', 'max:120'],
            'social_tiktok' => ['nullable', 'string', 'max:120'],
        ]);

        foreach (self::KEYS as $key) {
            Setting::put($key, trim((string) ($validated[$key] ?? '')));
        }

        return redirect()->route('admin.social-media.index')
            ->with('success', 'Pengaturan Social Media disimpan.');
    }
}
