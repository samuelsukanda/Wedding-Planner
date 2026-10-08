<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Support\ChecklistTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function index()
    {
        if (Auth::user()->wedding_id) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.index');
    }

    /**
     * Simpan data pernikahan sekaligus seed template checklist.
     *
     * Seluruh langkah wizard berjalan di client (Alpine), jadi request ini
     * baru datang sekali, saat user menekan "Mulai Perencanaan".
     */
    public function finish(Request $request)
    {
        $user = Auth::user();

        if ($user->wedding_id) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'user_name' => ['required', 'string', 'max:255'],
            'partner_name' => ['required', 'string', 'max:255'],
            'wedding_date' => ['required', 'date'],
            'total_budget' => ['required', 'numeric', 'min:1'],
        ], [
            'partner_name.required' => 'Nama pasangan wajib diisi.',
            'wedding_date.required' => 'Tanggal pernikahan wajib diisi.',
            'total_budget.required' => 'Target anggaran wajib diisi.',
            'total_budget.min' => 'Target anggaran harus lebih dari 0.',
        ]);

        $wedding = Wedding::create([
            'groom_name' => $validated['user_name'],
            'bride_name' => $validated['partner_name'],
            'wedding_date' => $validated['wedding_date'],
            'total_budget' => $validated['total_budget'],
        ]);

        ChecklistTemplate::seedFor($wedding);
        $user->update(['wedding_id' => $wedding->id]);

        $request->session()->forget('onboarding');
        $request->session()->forget('onboarding_step');

        return redirect()->route('dashboard')
            ->with('success', 'Workspace pernikahan sudah siap. Selamat merencanakan!');
    }
}