<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Support\ChecklistTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    private const STEP_FIELDS = ['user_name', 'partner_name', 'wedding_date', 'total_budget'];

    private const STEP_COUNT = 4;

    public function index(Request $request)
    {
        if (Auth::user()->wedding_id) {
            return redirect()->route('dashboard');
        }

        $progress = $request->session()->get('onboarding', []);
        $step = (int) $request->session()->get('onboarding_step', 1);

        return view('onboarding.index', [
            'step' => max(1, min($step, self::STEP_COUNT + 1)),
            'data' => $progress,
        ]);
    }

    public function store(Request $request)
    {
        if (Auth::user()->wedding_id) {
            return redirect()->route('dashboard');
        }

        // Tombol "Kembali" pada langkah 2-4 memakai tombol submit yang sama,
        // jadi arahannya ditentukan lewat field `direction`.
        if ($request->input('direction') === 'back') {
            return $this->back($request);
        }

        $validated = $request->validate([
            'user_name' => ['nullable', 'string', 'max:255'],
            'partner_name' => ['nullable', 'string', 'max:255'],
            'wedding_date' => ['nullable', 'date', 'after:today'],
            'total_budget' => ['nullable', 'numeric', 'min:0'],
        ]);

        $request->session()->put('onboarding', array_merge(
            $request->session()->get('onboarding', []),
            array_filter($validated, fn ($value) => $value !== null && $value !== '')
        ));

        $targetStep = self::nextStep($request);

        // Setelah langkah terakhir, pindah ke langkah konfirmasi.
        // Data pernikahan baru dibuat saat user menekan "Mulai Perencanaan".
        $request->session()->put('onboarding_step', $targetStep);

        return redirect()->route('onboarding.index');
    }

    public function finish(Request $request)
    {
        $data = $request->session()->get('onboarding', []);
        $user = Auth::user();

        if ($user->wedding_id) {
            return redirect()->route('dashboard');
        }

        $validated = validator(
            [
                'partner_name' => $data['partner_name'] ?? null,
                'wedding_date' => $data['wedding_date'] ?? null,
                'total_budget' => $data['total_budget'] ?? null,
            ],
            [
                'partner_name' => ['required', 'string', 'max:255'],
                'wedding_date' => ['required', 'date'],
                'total_budget' => ['required', 'numeric', 'min:0'],
            ],
            [
                'partner_name' => 'nama pasangan',
                'wedding_date' => 'tanggal pernikahan',
                'total_budget' => 'target anggaran',
            ]
        )->validate();

        $wedding = Wedding::create([
            'groom_name' => $data['user_name'] ?? $user->name,
            'bride_name' => $validated['partner_name'],
            'wedding_date' => $validated['wedding_date'],
            'total_budget' => $validated['total_budget'],
        ]);

        ChecklistTemplate::seedFor($wedding);
        $user->update(['wedding_id' => $wedding->id]);

        $request->session()->forget(['onboarding', 'onboarding_step']);

        return redirect()->route('dashboard')
            ->with('success', 'Workspace pernikahan sudah siap. Selamat merencanakan!');
    }

    public function back(Request $request)
    {
        $step = (int) $request->session()->get('onboarding_step', 1);

        $request->session()->put('onboarding_step', max(1, $step - 1));

        return redirect()->route('onboarding.index');
    }

    private function nextStep(Request $request): int
    {
        $step = (int) $request->session()->get('onboarding_step', 1);
        $data = $request->session()->get('onboarding', []);

        $field = self::STEP_FIELDS[$step - 1] ?? null;

        // Langkah dianggap selesai hanya kalau field wajibnya sudah terisi.
        if ($field === null || ! empty($data[$field])) {
            return $step + 1;
        }

        return $step;
    }
}