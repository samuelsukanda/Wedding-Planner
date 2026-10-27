<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'wedding_id', 'is_superadmin', 'profile_photo', 'auth_provider'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
        ];
    }

    /**
     * Status akun (free/pro) sebenarnya milik Wedding Kit, tapi panel admin
     * menampilkan user per baris. Accessor ini supaya $user->plan bisa dipakai
     * langsung, termasuk di json_encode() untuk form edit.
     *
     * @var array<int, string>
     */
    protected $appends = ['plan', 'partner_name', 'partner_email', 'add_on'];

    public function getPlanAttribute(): ?string
    {
        return $this->wedding?->plan;
    }

    public function getAddOnAttribute(): string
    {
        return $this->wedding?->photobooth_active ? 'photobooth' : 'none';
    }

    public function getPartnerNameAttribute(): ?string
    {
        return $this->partners()->first()?->name;
    }

    public function getPartnerEmailAttribute(): ?string
    {
        return $this->partners()->first()?->email;
    }

    /**
     * User yang berpasangan memakai wedding_id yang sama, jadi data mereka saling terlihat.
     */
    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    /**
     * Pasangan = user lain yang memakai Wedding Kit yang sama.
     *
     * Tidak perlu tabel atau kolom baru: panel admin cukup menunjuk email
     * pasangan, lalu keduanya berbagi wedding_id dan otomatis saling
     * melihat data.
     */
    public function partners()
    {
        return $this->hasMany(self::class, 'wedding_id', 'wedding_id')
            ->where('users.id', '!=', $this->getKey());
    }

    /**
     * URL foto profil, atau null kalau user belum memasang foto.
     */
    public function avatarUrl(): ?string
    {
        return $this->profile_photo
            ? Storage::disk('public')->url($this->profile_photo)
            : null;
    }

    /**
     * True kalau akun ini pernah/terakhir masuk lewat Google OAuth.
     * Akun daftar email tidak punya badge, jadi pemanggil cukup mengecek ini.
     */
    public function isGoogle(): bool
    {
        return $this->auth_provider === 'google';
    }

    /**
     * Huruf awal untuk avatar cadangan saat foto belum diunggah.
     */
    public function initial(): string
    {
        return mb_strtoupper(mb_substr($this->name ?: '?', 0, 1));
    }
}
