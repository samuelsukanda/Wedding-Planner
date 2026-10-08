<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Ganti foto profil. File lama dihapus supaya folder upload tidak
     * menumpuk file yang sudah tidak terpakai.
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        $this->deletePhoto($user);

        $user->forceFill([
            'profile_photo' => $request->file('photo')->store('avatars', 'public'),
        ])->save();

        return redirect()->route('admin.index')
            ->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->deletePhoto($user);

        $user->forceFill(['profile_photo' => null])->save();

        return redirect()->route('admin.index')
            ->with('success', 'Foto profil berhasil dihapus.');
    }

    private function deletePhoto($user): void
    {
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
    }
}
