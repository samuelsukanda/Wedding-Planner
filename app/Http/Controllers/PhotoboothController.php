<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Wedding;
use App\Services\PhotoboothApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PhotoboothController extends Controller
{
    private const FRESH_SNAPSHOT_TTL = 60;

    private const SNAPSHOT_TTL = 600;

    public function __construct(private readonly PhotoboothApi $api) {}

    public function index()
    {
        $wedding = Wedding::current();

        // Belum punya add-on: yang tampil halaman pembelian, bukan halaman
        // pengaturan. Photobooth berdiri sendiri, bukan bagian paket Pro.
        if (! $wedding?->photobooth_active) {
            return view('photobooth.locked', [
                'wedding' => $wedding,
                'price' => (int) Setting::get('photobooth_price', 0),
                'banks' => Setting::proBanks(),
                'paymentNote' => Setting::get('pro_payment_note', ''),
                'pending' => $wedding->upgradeRequests()
                    ->where('feature', 'photobooth')
                    ->where('status', 'pending')
                    ->latest()
                    ->first(),
            ]);
        }

        $event = null;
        $photos = [];
        $offline = false;

        if ($wedding?->photobooth_active && $wedding->photobooth_event_slug && $wedding->photoboothApiKey()) {
            $freshSnapshot = Cache::get($this->freshSnapshotKey($wedding));

            if ($freshSnapshot) {
                $event = $freshSnapshot['event'];
                $photos = $freshSnapshot['photos'];
            } else {
                try {
                    $responses = $this->api->dashboard($wedding);

                    if ($responses['event'] instanceof \Throwable) {
                        throw $responses['event'];
                    }
                    if ($responses['photos'] instanceof \Throwable) {
                        throw $responses['photos'];
                    }

                    $responses['event']->throw();
                    $responses['photos']->throw();

                    $event = $responses['event']->json();
                    $photos = $responses['photos']->json() ?: [];
                    $snapshot = ['event' => $event, 'photos' => $photos];

                    Cache::put($this->snapshotKey($wedding), $snapshot, self::SNAPSHOT_TTL);
                    Cache::put($this->freshSnapshotKey($wedding), $snapshot, self::FRESH_SNAPSHOT_TTL);
                } catch (\Throwable $e) {
                    report($e);
                    $offline = true;

                    $snapshot = Cache::get($this->snapshotKey($wedding)) ?: [];
                    $event = $snapshot['event'] ?? null;
                    $photos = $snapshot['photos'] ?? [];
                }
            }
        }

        return view('photobooth.index', compact('wedding', 'event', 'photos', 'offline'));
    }

    public function update(Request $request)
    {
        $wedding = Wedding::current();
        if (! $wedding?->photobooth_active) {
            return $this->lockedResponse();
        }

        $validated = $request->validate([
            'event_slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'api_key' => ['required', 'string', 'max:255'],
        ]);

        $wedding->update([
            'photobooth_event_slug' => $validated['event_slug'],
            'photobooth_api_key' => $validated['api_key'],
            'photobooth_active' => $request->boolean('active'),
        ]);
        $this->forgetSnapshot($wedding);

        return redirect()->route('photobooth.index')->with('success', 'Koneksi Photobooth tersimpan.');
    }

    /**
     * Aktivasi Photobooth untuk pasangan ini.
     *
     * Event dibuat di sisi Photobooth ( provisioning ), lalu slug & api_key
     * disimpan di sini. Event yang sudah pernah dibuat hanya diperbarui,
     * jadi QR yang sudah tercetak tetap hidup.
     */
    public function activate()
    {
        $wedding = Wedding::current();
        if (! $wedding?->photobooth_active) {
            return $this->lockedResponse();
        }

        $couple = trim($wedding->groom_name.' & '.$wedding->bride_name);

        $payload = [
            'title' => $couple.' Wedding',
            'groom_name' => $wedding->groom_name,
            'bride_name' => $wedding->bride_name,
            'wedding_date' => $wedding->wedding_date?->format('Y-m-d'),
            'frame_text' => $couple,
            'frame_font' => config('photobooth.default_font'),
        ];

        if ($wedding->photobooth_event_slug) {
            $payload['slug'] = $wedding->photobooth_event_slug;
        }

        try {
            $response = $this->api->provision($payload)->throw();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Photobooth sedang tidak dapat dihubungi. Coba lagi beberapa saat lagi.');
        }

        $wedding->update([
            'photobooth_event_slug' => $response->json('slug'),
            'photobooth_api_key' => $response->json('api_key'),
            'photobooth_active' => true,
        ]);
        $this->forgetSnapshot($wedding);

        return redirect()->route('photobooth.index')
            ->with('success', 'Photobooth aktif. QR dan pengaturan sudah siap.');
    }

    /**
     * Pembelian add-on Photobooth lewat transfer manual.
     *
     * Tidak ada payment gateway: pasangan transfer ke rekening yang
     * dicantumkan superadmin, lalu mengunggah bukti. Aktivasi tetap
     * dilakukan superadmin dari daftar request.
     */
    public function purchase(Request $request)
    {
        $wedding = Wedding::current();

        abort_if(! $wedding, 403, 'Akun belum terhubung ke acara pernikahan.');

        $validated = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $created = DB::transaction(function () use ($wedding, $request, $validated) {
            $wedding = $wedding->newQuery()->lockForUpdate()->findOrFail($wedding->id);

            if ($wedding->upgradeRequests()->where('feature', 'photobooth')->where('status', 'pending')->exists()) {
                return false;
            }

            $wedding->upgradeRequests()->create([
                'user_id' => $request->user()->id,
                'feature' => 'photobooth',
                'plan' => $wedding->plan,
                'amount' => (int) Setting::get('photobooth_price', 0),
                'proof_path' => $validated['proof']->store('upgrade-proofs', 'local'),
                'status' => 'pending',
            ]);

            return true;
        });

        if (! $created) {
            return redirect()->route('photobooth.index')
                ->with('error', 'Kamu sudah mengirim bukti transfer dan masih menunggu konfirmasi admin.');
        }

        return redirect()->route('photobooth.index')
            ->with('success', 'Bukti transfer terkirim. Menunggu konfirmasi admin.');
    }

    public function settings(Request $request)
    {
        $wedding = Wedding::current();
        if (! $wedding?->photobooth_active) {
            return $this->lockedResponse();
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'groom_name' => 'required|string|max:100',
            'bride_name' => 'required|string|max:100',
            'gallery_mode' => 'required|in:public,private',
            'frame_text' => 'nullable|string|max:40',
            'frame_font' => 'nullable|string|max:50',
        ]);

        // Tanggal bukan inputan: sumbernya data pernikahan di aplikasi ini.
        $validated['wedding_date'] = $wedding->wedding_date?->format('Y-m-d');

        return $this->guard(
            fn () => $this->api->updateEvent($wedding, $validated)->throw(),
            'Pengaturan Photobooth tersimpan.',
            $wedding
        );
    }

    public function banner(Request $request)
    {
        if (! Wedding::current()?->photobooth_active) {
            return $this->lockedResponse();
        }

        $request->validate(['banner' => 'required|image|max:5120']);
        $wedding = Wedding::current();

        return $this->guard(function () use ($request, $wedding) {
            $this->api->for($wedding)
                ->attach('banner', fopen($request->file('banner')->getRealPath(), 'r'), $request->file('banner')->getClientOriginalName())
                ->post('/api/admin/banner')
                ->throw();
        }, 'Banner Photobooth diperbarui.', $wedding);
    }

    public function deletePhoto(int $photo)
    {
        if (! Wedding::current()?->photobooth_active) {
            return $this->lockedResponse();
        }

        $wedding = Wedding::current();

        return $this->guard(
            fn () => $this->api->for($wedding)->delete("/api/admin/photos/{$photo}")->throw(),
            'Foto dihapus.',
            $wedding
        );
    }

    public function export()
    {
        if (! Wedding::current()?->photobooth_active) {
            return $this->lockedResponse();
        }

        try {
            $response = $this->api->for(Wedding::current())->get('/api/admin/photos/export.zip')->throw();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Photobooth sedang tidak dapat dihubungi. File ZIP belum bisa diunduh.');
        }

        return response($response->body(), 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="photobooth.zip"',
        ]);
    }

    /**
     * Jalankan pemanggilan API ke Photobooth. Kalau Photobooth mati, hasilnya
     * pesan error di halaman, bukan 500.
     */
    private function guard(callable $action, string $successMessage, ?Wedding $wedding = null)
    {
        try {
            $action();
        } catch (\Throwable $e) {
            report($e);

            if ($wedding) {
                $this->forgetFreshSnapshot($wedding);
            }

            return back()->with('error', 'Photobooth sedang tidak dapat dihubungi. Coba lagi beberapa saat lagi.');
        }

        if ($wedding) {
            $this->forgetSnapshot($wedding);
        }

        return back()->with('success', $successMessage);
    }

    private function lockedResponse()
    {
        return redirect()->route('photobooth.index')
            ->with('error', 'Photobooth belum bisa digunakan. Beli add-on Photobooth Digital terlebih dahulu.');
    }

    private function forgetFreshSnapshot(Wedding $wedding): void
    {
        Cache::forget($this->freshSnapshotKey($wedding));
    }

    private function forgetSnapshot(Wedding $wedding): void
    {
        Cache::forget($this->snapshotKey($wedding));
        $this->forgetFreshSnapshot($wedding);
    }

    private function freshSnapshotKey(Wedding $wedding): string
    {
        return "photobooth:fresh-snapshot:{$wedding->id}";
    }

    private function snapshotKey(Wedding $wedding): string
    {
        return "photobooth:snapshot:{$wedding->id}";
    }
}
