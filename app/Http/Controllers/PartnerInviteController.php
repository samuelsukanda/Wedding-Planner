<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Mengajak pasangan bergabung ke Wedding Kit yang sama lewat tautan.
 *
 * Hanya akun berpaket Pro yang boleh membuat tautan. Yang membuka tautan tidak
 * harus login - belum punya akun sama sekali - dan yang membuat password untuk
 * akunnya sendiri.
 */
class PartnerInviteController extends Controller
{
    /** Halaman kelola undangan: pemilik Wedding Kit. */
    public function index()
    {
        $wedding = Wedding::current();

        abort_if(! $wedding?->isPro(), 403, 'Tautan undangan hanya tersedia untuk paket Pro.');

        return view('profile.partner-invites', [
            'wedding' => $wedding,
            'invites' => $wedding->invites()->with('acceptedUser')->latest()->get(),
            // Dipakai view supaya tautan di dalam wa.me memakai host yang sama
            // dengan halaman ini, termasuk saat diuji lewat environment lokal.
            'inviteUrl' => fn (WeddingInvite $invite) => route('invite.accept', $invite->token),
        ]);
    }

    public function store(Request $request)
    {
        $wedding = Wedding::current();

        abort_if(! $wedding?->isPro(), 403, 'Tautan undangan hanya tersedia untuk paket Pro.');

        // Satu undangan aktif saja: menumpuk beberapa tautan untuk satu
        // pasangan hanya menambah cara untuk salah kirim.
        WeddingInvite::where('wedding_id', $wedding->id)
            ->whereNull('accepted_at')
            ->delete();

        $invite = $wedding->invites()->create([
            'token' => Str::random(48),
            'expires_at' => now()->addDays(WeddingInvite::VALID_DAYS),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('profile.partner-invites.index')
            ->with('success', 'Link undangan siap dibagikan.');
    }

    public function destroy(WeddingInvite $invite)
    {
        $this->authorizeInvite($invite);

        $invite->delete();

        return redirect()->route('profile.partner-invites.index')
            ->with('success', 'Link undangan dibatalkan.');
    }

    /** Halaman undangan: publik, belum login pun boleh membuka. */
    public function show(string $token)
    {
        $invite = $this->findUsableInvite($token);

        return view('invites.accept', ['invite' => $invite]);
    }

    public function accept(Request $request, string $token)
    {
        $invite = $this->findUsableInvite($token);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Email yang sudah terdaftar ditolak, bukan diam-diam dipindahkan ke
            // Wedding Kit ini: pemilik email itu bisa jadi sedang memakai Wedding
            // Kit lain dan kehilangan aksesnya.
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk dengan akun yang sudah ada.',
        ], [], [
            'name' => 'nama',
            'email' => 'email',
            'password' => 'password',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'wedding_id' => $invite->wedding_id,
        ]);

        $invite->update([
            'accepted_at' => now(),
            'accepted_user_id' => $user->id,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Selamat datang! Kamu sudah bergabung ke Wedding Kit pasanganmu.');
    }

    private function authorizeInvite(WeddingInvite $invite): void
    {
        abort_if($invite->wedding_id !== Wedding::current()?->id, 403);
    }

    /**
     * Undangan yang sudah dipakai atau kedaluwarsa tidak boleh dibuka lagi,
     * dan alasannya harus berbeda: "sudah dipakai" bukan "tidak ditemukan",
     * supaya pemilik Wedding Kit bisa paham link-nya kenapa mati.
     */
    private function findUsableInvite(string $token): WeddingInvite
    {
        $invite = WeddingInvite::with('wedding')->where('token', $token)->first();

        abort_if(! $invite, 404, 'Link undangan tidak ditemukan.');

        if ($invite->accepted_at) {
            abort(410, 'Link undangan ini sudah pernah dipakai.');
        }

        if ($invite->expires_at->isPast()) {
            abort(410, 'Link undangan ini sudah kedaluwarsa. Minta pasangan membuat link baru.');
        }

        return $invite;
    }
}