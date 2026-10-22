<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Wedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'bride_name',
        'groom_name',
        'wedding_date',
        'total_budget',
        'location',
        'notes',
        'wa_template',
        'plan',
        'photobooth_event_slug',
        'photobooth_api_key',
        'photobooth_active',
    ];

    protected $casts = [
        'wedding_date' => 'date',
        'total_budget' => 'decimal:2',
        'photobooth_active' => 'boolean',
    ];

    /**
     * Nama depan saja — dipakai untuk tampilan (judul, sidebar, header, cetak, export).
     */
    private function firstName(?string $full): string
    {
        $full = trim((string) $full);

        return $full === '' ? '' : explode(' ', $full)[0];
    }

    public function getGroomFirstNameAttribute(): string
    {
        return $this->firstName($this->groom_name);
    }

    public function getBrideFirstNameAttribute(): string
    {
        return $this->firstName($this->bride_name);
    }

    public function getCoupleNameAttribute(): string
    {
        return implode(' & ', array_filter([$this->groom_first_name, $this->bride_first_name]));
    }

    /**
     * Wedding milik user yang sedang login. Semua user yang berbagi pasangan memakai
     * wedding_id yang sama, sehingga mereka melihat data yang identik.
     */
    public static function current(): ?self
    {
        return auth()->user()?->wedding;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function catalogReviews()
    {
        return $this->hasMany(VendorCatalogReview::class);
    }

    /**
     * Paket berbayar. Disimpan di weddings, bukan users, karena satu Wedding Kit
     * dipakai dua akun sekaligus (suami + istri) dan cukup satu yang upgrade.
     */
    public function isPro(): bool
    {
        return $this->plan === 'pro';
    }

    /**
     * Satu-satunya tempat yang memutuskan sebuah fitur berbayar boleh dibuka.
     * Middleware dan ikon gembok di sidebar sama-sama memakai ini supaya
     * keduanya tidak bisa berbeda pendapat.
     */
    public function hasFeature(string $feature): bool
    {
        $required = config("plans.features.{$feature}");

        // Fitur tidak terdaftar di config = fitur gratis.
        return $required === null || $this->plan === $required;
    }

    public function photoboothApiKey(): ?string
    {
        if (! $this->photobooth_api_key) {
            return null;
        }

        try {
            return Crypt::decryptString($this->photobooth_api_key);
        } catch (\Throwable) {
            return $this->photobooth_api_key;
        }
    }

    public function setPhotoboothApiKeyAttribute(?string $value): void
    {
        $this->attributes['photobooth_api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function upgradeRequests()
    {
        return $this->hasMany(UpgradeRequest::class);
    }

    public function invites()
    {
        return $this->hasMany(WeddingInvite::class);
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function vendorContracts()
    {
        return $this->hasMany(VendorContract::class);
    }

    public function vendorPayments()
    {
        return $this->hasMany(VendorPayment::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function moodboards()
    {
        return $this->hasMany(Moodboard::class);
    }

    public function rundownEvents()
    {
        return $this->hasMany(RundownEvent::class);
    }

    public function gifts()
    {
        return $this->hasMany(Gift::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Module 12 - Tabungan Pernikahan. Satu wedding bisa punya lebih dari satu
     * target tabungan (PRD Business Rule 1).
     */
    public function savingsGoals()
    {
        return $this->hasMany(SavingsGoal::class);
    }

    public function savingsTransactions()
    {
        return $this->hasMany(SavingsTransaction::class);
    }

    /**
     * Module 13 - Lamaran. Satu acara lamaran per wedding.
     */
    public function proposalEvent()
    {
        return $this->hasOne(ProposalEvent::class);
    }

    public function proposalChecklists()
    {
        return $this->hasMany(ProposalChecklist::class);
    }

    public function proposalGuests()
    {
        return $this->hasMany(ProposalGuest::class);
    }

    public function proposalBudgets()
    {
        return $this->hasMany(ProposalBudget::class);
    }

    /**
     * Module 14 - Seserahan.
     */
    public function souvenirs()
    {
        return $this->hasMany(Souvenir::class);
    }

    /**
     * Module 15 - Persyaratan Nikah.
     */
    public function documentRequirements()
    {
        return $this->hasMany(DocumentRequirement::class);
    }
}
