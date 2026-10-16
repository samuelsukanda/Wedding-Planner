<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Review & rating pasangan untuk vendor katalog (Cari Vendor).
 *
 * Dipisah dari kolom reviews (JSON) di vendor_catalog yang berisi review
 * hasil import Weddingku. Satu wedding cukup satu review per vendor.
 */
class VendorCatalogReview extends Model
{
    protected $fillable = [
        'vendor_catalog_id',
        'wedding_id',
        'rating',
        'review',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function catalog()
    {
        return $this->belongsTo(VendorCatalog::class, 'vendor_catalog_id');
    }

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    /**
     * Rata-rata rating pengguna untuk satu vendor, null kalau belum ada.
     */
    public static function averageFor(VendorCatalog $catalog): ?float
    {
        $average = static::where('vendor_catalog_id', $catalog->id)->avg('rating');

        return $average !== null ? (float) $average : null;
    }
}
