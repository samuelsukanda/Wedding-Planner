<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Wedding;
use App\Services\PhotoboothApi;
use Illuminate\Http\Request;

/**
 * Pengaturan add-on Photobooth yang dikelola superadmin: harga pembelian dan
 * batas jumlah foto per event.
 */
class AdminPhotoboothController extends Controller
{
    public function edit()
    {
        return view('admin.photobooth-settings', [
            'price' => (int) Setting::get('photobooth_price', 0),
            'quota' => (int) Setting::get('photobooth_photo_quota', 300),
            'events' => Wedding::where('photobooth_active', true)->get(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'photobooth_price' => ['required', 'integer', 'min:0'],
            'photobooth_photo_quota' => ['required', 'integer', 'min:0'],
        ]);

        Setting::put('photobooth_price', (string) $validated['photobooth_price']);
        Setting::put('photobooth_photo_quota', (string) $validated['photobooth_photo_quota']);

        // Kuota baru langsung disiarkan ke event yang sudah aktif, supaya
        // perubahan ini berlaku tanpa menunggu pasangan membuka halaman.
        foreach (Wedding::where('photobooth_active', true)->get() as $wedding) {
            if (! $wedding->photoboothApiKey()) {
                continue;
            }

            try {
                app(PhotoboothApi::class)->updateEvent($wedding, [
                    'photo_quota' => (int) $validated['photobooth_photo_quota'],
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('admin.photobooth-settings.index')
            ->with('success', 'Pengaturan Photobooth tersimpan.');
    }
}
