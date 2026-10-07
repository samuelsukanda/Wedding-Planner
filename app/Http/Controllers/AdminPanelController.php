<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\DropdownOption;
use App\Models\User;
use App\Support\ChecklistTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminPanelController extends Controller
{
    public function index()
    {
        // Jangan auto-create weddings di sini. Superadmin sengaja tidak punya
        // weddings, dan view sudah menampilkan empty state kalau null.
        $wedding = Wedding::current();

        return view('admin.index', compact('wedding'));
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
            'total_budget' => 'required|numeric|min:0',
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

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui!');
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
