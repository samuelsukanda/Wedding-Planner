<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        if (! config('services.google.client_id')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login dengan Google belum dikonfigurasi. Silakan hubungi admin.',
            ]);
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

        // Google menjamin email terverifikasi, jadi email cukup sebagai identitas.
        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'auth_provider' => 'google',
            ]);
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