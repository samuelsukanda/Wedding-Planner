<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
            ]);
        }

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
}