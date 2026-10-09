<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'wedding_date' => 'date',
        'total_budget' => 'decimal:2',
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
}
