<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\DropdownOption;
use App\Models\User;
use App\Support\ChecklistTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminPanelController extends Controller
{
    public function index()
    {
        // Jangan auto-create weddings di sini. Superadmin sengaja tidak punya
        // weddings, dan view sudah menampilkan empty state kalau null.
        $wedding = Wedding::current();

        return view('admin.index', [
            'wedding' => $wedding,
            'me' => auth()->user(),
        ]);
    }

    public function masterData(Request $request)
    {
        $groups = DropdownOption::getGroups();
        $selectedGroup = $request->query('group', 'checklist_category');

        if (!array_key_exists($selectedGroup, $groups)) {
            $selectedGroup = 'checklist_category';
        }

        $options = DropdownOption::where('group_key', $selectedGroup)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Also get counts for each group badge
        $groupCounts = DropdownOption::selectRaw('group_key, count(*) as total')
            ->groupBy('group_key')
            ->pluck('total', 'group_key')
            ->toArray();

        return view('admin.master-data', compact('groups', 'selectedGroup', 'options', 'groupCounts'));
    }

    public function updateWedding(Request $request, Wedding $wedding)
    {
        $request->merge([
            'total_budget' => preg_replace('/\\D/', '', (string) $request->input('total_budget')),
        ]);

        // Pengguna hanya boleh mengubah wedding miliknya sendiri.
        abort_unless(
            $request->user()?->wedding_id === $wedding->id,
            403,
            'Anda tidak berhak mengubah data acara pasangan lain.'
        );

        $validated = $request->validate([
            'bride_name' => 'required|string|max:255',
            'groom_name' => 'required|string|max:255',
            'wedding_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'total_budget' => $this->moneyRule(),
            'notes' => 'nullable|string',
        ]);

        $wedding->update($validated);

        return redirect()->route('admin.index')
            ->with('success', 'Informasi acara pernikahan berhasil diperbarui!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_key' => 'required|string',
            'option_value' => 'required|string|max:255',
            'meta_value' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $groups = DropdownOption::getGroups();
        $groupName = $groups[$request->group_key] ?? 'Master Option';

        DropdownOption::create([
            'group_key' => $validated['group_key'],
            'group_name' => $groupName,
            'option_value' => trim($validated['option_value']),
            'meta_value' => $validated['meta_value'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.master-data.index', ['group' => $validated['group_key']])
            ->with('success', 'Pilihan dropdown berhasil ditambahkan!');
    }

    public function update(Request $request, DropdownOption $dropdownOption)
    {
        $validated = $request->validate([
            'option_value' => 'required|string|max:255',
            'meta_value' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $dropdownOption->update([
            'option_value' => trim($validated['option_value']),
            'meta_value' => $validated['meta_value'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.master-data.index', ['group' => $dropdownOption->group_key])
            ->with('success', 'Pilihan dropdown berhasil diperbarui!');
    }

    public function destroy(DropdownOption $dropdownOption)
    {
        $groupKey = $dropdownOption->group_key;
        $dropdownOption->delete();

        return redirect()->route('admin.master-data.index', ['group' => $groupKey])
            ->with('success', 'Pilihan dropdown berhasil dihapus!');
    }

    // ==========================================================
    // ADMIN PANEL — Manajemen User
    // ==========================================================

    public function users()
    {
        $users = User::with('wedding')->orderBy('id')->get();
        $weddings = Wedding::orderBy('id')->get(['id', 'groom_name', 'bride_name']);

        return view('admin.users', compact('users', 'weddings'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'wedding_mode' => 'required|in:existing,new',
            'wedding_id' => 'required_if:wedding_mode,existing|nullable|exists:weddings,id',
            'groom_name' => 'required_if:wedding_mode,new|nullable|string|max:255',
            'bride_name' => 'required_if:wedding_mode,new|nullable|string|max:255',
            'wedding_date' => 'required_if:wedding_mode,new|nullable|date',
        ]);

        $weddingId = $validated['wedding_id'] ?? null;

        if ($validated['wedding_mode'] === 'new') {
            $wedding = Wedding::create([
                'bride_name' => $validated['bride_name'],
                'groom_name' => $validated['groom_name'],
                'wedding_date' => $validated['wedding_date'],
                'total_budget' => 0,
            ]);

            // Setiap pasangan baru langsung dapat checklist awal.
            ChecklistTemplate::seedFor($wedding);

            $weddingId = $wedding->id;
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'wedding_id' => $weddingId,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'wedding_id' => 'nullable|exists:weddings,id',
            'is_superadmin' => 'nullable|boolean',
            'plan' => 'nullable|in:free,pro',
            'partner_name' => 'nullable|string|max:255',
            // Sengaja TANPA unique: email pasangan yang sudah terdaftar justru
            // harus bisa dipakai untuk menautkan akun itu ke Wedding Kit ini.
            'partner_email' => ['nullable', 'email', 'max:255', 'not_in:'.$user->email],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'wedding_id' => $validated['wedding_id'] ?? null,
            'is_superadmin' => $request->boolean('is_superadmin'),
        ]);

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        // Status akun (free/pro) menempel pada Wedding Kit, bukan pada User,
        // jadi di sini belong dipakai. Disetel setelah save supaya mengikuti
        // wedding yang benar kalau sekalian user-nya dipindah ke wedding lain.
        if (! empty($validated['plan']) && $user->wedding) {
            $user->wedding->update(['plan' => $validated['plan']]);
        }

        $this->syncPartner($user, $validated['partner_email'] ?? null, $validated['partner_name'] ?? null);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui!');
    }

    /**
     * Form cepat dari dropdown di list user: hanya menyimpan data pasangan,
     * tanpa menyentuh nama/email/password akunnya sendiri.
     */
    public function updatePartner(Request $request, User $user)
    {
        $validated = $request->validate([
            'partner_name' => 'nullable|string|max:255',
            'partner_email' => [
                'required',
                'email',
                'max:255',
                'not_in:'.$user->email,
            ],
        ]);

        $this->syncPartner($user, $validated['partner_email'], $validated['partner_name'] ?? null);

        return redirect()->route('admin.users.index')->with('success', 'Pasangan berhasil ditambahkan!');
    }

    /**
     * Menautkan pasangan ke Wedding Kit yang sama.
     *
     * Kalau email-nya sudah terdaftar, akun itu yang dipakai (dipindahkan ke
     * wedding yang sama) sehingga pasangan bisa langsung login. Kalau belum,
     * dibuatkan akun baru dengan password acak -- pasangan memakai fitur
     * "Lupa Password" untuk mengganti passwordnya sendiri.
     */
    private function syncPartner(User $user, ?string $email, ?string $name): void
    {
        $email = trim((string) $email);

        // Tidak bisa dipasangkan ke dirinya sendiri, dan tanpa Wedding Kit
        // tidak ada workspace yang bisa dibagi.
        if ($email === '' || $email === $user->email || ! $user->wedding_id) {
            return;
        }

        $partner = User::where('email', $email)->first();

        if ($partner) {
            $partner->update(['wedding_id' => $user->wedding_id]);

            if (! empty($name)) {
                $partner->update(['name' => $name]);
            }

            return;
        }

        User::create([
            'name' => $name ?: Str::before($email, '@'),
            'email' => $email,
            'password' => Str::password(16),
            'wedding_id' => $user->wedding_id,
        ]);
    }

    public function destroyUser(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak bisa menghapus akun yang sedang login.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus!');
    }
}
