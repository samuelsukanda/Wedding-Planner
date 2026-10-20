<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PartnerInviteController;
use App\Models\User;
use App\Models\WeddingInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        if (! config('services.google.client_id')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login dengan Google belum dikonfigurasi. Silakan hubungi admin.',
            ]);
        }

        // Tombol Google di halaman undangan pasangan membawa token di URL.
        // Token tidak boleh jalan lewat state OAuth karena itu punya Socialite;
        // disimpan di sesi dan diambil satu kali di callback.
        if ($request->query('invite')) {
            $request->session()->put('pending_invite_token', (string) $request->query('invite'));
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $exception) {
            // Sebelumnya exception ditelan begitu saja sehingga kegagalan
            // OAuth tidak pernah bisa dicari penyebabnya lewat log.
            report($exception);

            $message = $exception instanceof \Laravel\Socialite\Two\InvalidStateException
                ? 'Sesi login Google kedaluwarsa. Silakan coba lagi.'
                : 'Login dengan Google gagal. Silakan coba lagi.';

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Google tidak menyediakan email.',
            ]);
        }

        // Token undangan dari tombol Google di halaman undangan, dipakai
        // satu kali di sini supaya tidak menghantui login Google berikutnya.
        $pendingInviteToken = (string) $request->session()->pull('pending_invite_token', '');
        $invite = null;

        if ($pendingInviteToken !== '') {
            $invite = WeddingInvite::with('wedding')->where('token', $pendingInviteToken)->first();

            if ($problem = app(PartnerInviteController::class)->inviteProblem($invite)) {
                return $problem;
            }
        }

        // Google menjamin email terverifikasi, jadi email cukup sebagai identitas.
        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'auth_provider' => 'google',
                // Akun baru lewat undangan langsung masuk ke Wedding Kit yang
                // sama - ekuivalen dengan mengisi form Daftar & Bergabung.
                'wedding_id' => $invite?->wedding_id,
            ]);

            if ($invite) {
                $invite->update([
                    'accepted_at' => now(),
                    'accepted_user_id' => $user->id,
                ]);
            }
        } elseif ($invite) {
            // Akun sudah ada: jangan pindahkan ke Wedding Kit lain, sama
            // seperti alur email yang menolak email yang sudah terdaftar.
            // Undangan biarkan tetap hidup dan cukup dipakai lewat email.
            $invite = null;
            $request->session()->flash('error',
                'Kamu masuk dengan akun yang sudah terdaftar, jadi tautan undangan tidak dipakai. Minta tautan baru kalau ingin bergabung dengan email Google ini.');
        }

        // Akun yang awalnya daftar via email pun ikut ditandai Google, supaya
        // badge selalu mencerminkan cara login terakhir.
        if (! $user->isGoogle()) {
            $user->forceFill(['auth_provider' => 'google'])->save();
        }

        $this->importGooglePhoto($user, $googleUser);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        if ($user->is_superadmin) {
            return redirect()->route('admin.users.index');
        }

        // Tanpa data pernikahan, user baru diarahkan ke wizard.
        if (! $user->wedding_id) {
            return redirect()->route('onboarding.index');
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Salin foto profil Google ke storage lokal supaya tampil di sidebar dan
     * halaman Profile tanpa bergantung pada URL Google.
     *
     * Semua kegagalan ditelan: foto Google yang hilang atau berubah URLnya
     * tidak boleh membuat login gagal.
     */
    private function importGooglePhoto(User $user, $googleUser): void
    {
        // Foto yang sudah diunggah user manual tidak ditimpa.
        if ($user->profile_photo) {
            return;
        }

        $url = $googleUser->getAvatar();

        if (! $url) {
            return;
        }

        try {
            $response = Http::timeout(10)->get($url);

            if (! $response->successful()) {
                return;
            }

            $contentType = (string) $response->header('Content-Type');

            // Jaga agar halaman error HTML tidak tersimpan sebagai gambar.
            if (! str_starts_with($contentType, 'image/')) {
                return;
            }

            $content = $response->body();

            if (strlen($content) > 2 * 1024 * 1024) {
                return;
            }

            $extension = match (true) {
                str_contains($contentType, 'png') => 'png',
                str_contains($contentType, 'webp') => 'webp',
                default => 'jpg',
            };

            $path = 'avatars/' . $user->id . '-' . time() . '.' . $extension;

            Storage::disk('public')->put($path, $content);

            $user->forceFill(['profile_photo' => $path])->save();
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}