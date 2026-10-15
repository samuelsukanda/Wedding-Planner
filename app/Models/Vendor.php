<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedToWedding;

/**
 * Module 5 - Vendor Management (data milik pasangan).
 *
 * Berbeda dengan VendorCatalog: tabel ini milik satu wedding dan ikut
 * di-scope ScopedToWedding. Kolom instagram/whatsapp diisi dari katalog
 * saat user menyimpan vendor hasil pencarian.
 */
class Vendor extends Model
{
    use HasFactory;
    use ScopedToWedding;

    protected $fillable = [
        'wedding_id',
        'proposal_event_id',
        'name',
        'category',
        'contact',
        'address',
        'google_maps_url',
        'instagram',
        'whatsapp',
        'package',
        'price',
        'rating',
        'review',
        'photo',
        'booking_status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'float',
    ];

    public function setRatingAttribute($value)
    {
        $this->attributes['rating'] = $value ?? 5.0;
    }

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function contracts()
    {
        return $this->hasMany(VendorContract::class);
    }

    public function payments()
    {
        return $this->hasMany(VendorPayment::class);
    }

    public function packageItems()
    {
        return $this->hasMany(VendorPackageItem::class);
    }
}
