<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
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
}
