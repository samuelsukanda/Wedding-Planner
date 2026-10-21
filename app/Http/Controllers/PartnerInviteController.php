<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingInvite;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $invite = WeddingInvite::with('wedding')->where('token', $token)->first();

        if ($problem = $this->inviteProblem($invite)) {
            return $problem;
        }

        return view('invites.accept', ['invite' => $invite]);
    }

    public function accept(Request $request, string $token)
    {
        $invite = WeddingInvite::with('wedding')->where('token', $token)->first();

        if ($problem = $this->inviteProblem($invite)) {
            return $problem;
        }

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

        $user = DB::transaction(function () use ($invite, $validated) {
        $invite = WeddingInvite::query()->lockForUpdate()->findOrFail($invite->id);
        abort_if($invite->accepted_at || $invite->expires_at->isPast(), 410, 'Link undangan sudah tidak berlaku.');
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
        return $user;
        });

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
     * Kenapa sebuah tautan tidak bisa dipakai lagi, atau null kalau masih bisa.
     *
     * Sengaja dikembalikan sebagai halaman aplikasi, bukan abort(): yang
     * membuka tautan belum punya akun dan sering salah klik / kirim tautan
     * lama, jadi halaman error Laravel yang mentah hanya menambah kebingungan.
     * Status HTTP-nya tetap 404/410 supaya mesin pencari dan cache tetap paham
     * tautannya mati.
     *
     * Ketiganya dibedakan karena jalannya berbeda: "tidak ditemukan" berarti
     * tautannya salah, "sudah dipakai" berarti orangnya mungkin sudah punya
     * akun, "kedaluwarsa" berarti harus minta tautan baru.
     *
     * Public karena dipakai ulang oleh GoogleAuthController: login Google di
     * halaman undangan harus lolos cek yang sama sebelum ikut memakai undangan. */
    public function inviteProblem(?WeddingInvite $invite): ?Response
    {
        if (! $invite) {
            return $this->problemPage(
                404,
                'fa-link-slash',
                'Link Undangan Tidak Ditemukan',
                'Tautan undangan tidak valid atau sudah tidak tersedia. Periksa kembali tautan yang kamu terima.',
                null,
            );
        }

        if ($invite->accepted_at) {
            return $this->problemPage(
                410,
                'fa-user-check',
                'Link Undangan Sudah Dipakai',
                'Tautan ini sudah pernah dipakai untuk membuat akun. Kalau itu akun kamu, langsung masuk saja.',
                'Belum punya akun?',
                route('register'),
            );
        }

        if ($invite->expires_at->isPast()) {
            return $this->problemPage(
                410,
                'fa-clock',
                'Link Undangan Kedaluwarsa',
                'Tautan undangan hanya berlaku '.WeddingInvite::VALID_DAYS.' hari. Minta yang mengirimnya membuat tautan baru.',
                'Belum punya akun?',
                route('register'),
            );
        }

        return null;
    }

    /** @param string|null $hintLead teks sebelum tautan, null kalau tanpa tautan */
    private function problemPage(int $code, string $icon, string $title, string $message, ?string $hintLead = null, ?string $hintLink = null): Response
    {
        return response()->view('invites.problem', [
            'title' => $title,
            'icon' => $icon,
            'message' => $message,
            'hint' => $hintLead,
            'hintLink' => $hintLink,
        ], $code);
    }
}
