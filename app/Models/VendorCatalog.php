<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Katalog vendor publik hasil import dari Weddingku.
 *
 * SENGAJA tidak memakai ScopedToWedding: katalog ini milik bersama semua
 * pasangan, bukan milik satu wedding. Yang milik user ada di tabel vendors.
 */
class VendorCatalog extends Model
{
    use HasFactory;

    protected $table = 'vendor_catalog';

    protected $fillable = [
        'source',
        'source_url',
        'vendor_id',
        'slug',
        'name',
        'category',
        'city',
        'province',
        'address',
        'phone',
        'whatsapp',
        'instagram',
        'rating',
        'review_count',
        'starting_price',
        'price_label',
        'venue_rooms',
        'album',
        'album_count',
        'reviews',
        'about',
        'photo',
        'scraped_at',
    ];

    protected $casts = [
        'vendor_id' => 'integer',
        'rating' => 'float',
        'review_count' => 'integer',
        'album_count' => 'integer',
        'starting_price' => 'decimal:2',
        'venue_rooms' => 'array',
        'album' => 'array',
        'reviews' => 'array',
        'scraped_at' => 'datetime',
    ];

    public function userReviews()
    {
        return $this->hasMany(VendorCatalogReview::class, 'vendor_catalog_id');
    }

    /**
     * URL detail memakai slug vendor, bukan id, supaya lebih enak dibaca.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Kategori & kota unik untuk filter di halaman Cari Vendor.
     */
    public static function facets(string $column): array
    {
        return static::query()
            ->whereNotNull($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }
}
