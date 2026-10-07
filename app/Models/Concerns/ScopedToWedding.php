<?php

namespace App\Models\Concerns;

use App\Models\Wedding;
use Illuminate\Database\Eloquent\Builder;

/**
 * Membatasi query ke wedding milik user yang sedang login.
 *
 * Dipasang sebagai global scope supaya route model binding ikut aman: record milik
 * wedding lain otomatis tidak ditemukan (404), jadi user tidak bisa mengedit data
 * pasangan lain hanya dengan menebak ID di URL.
 *
 * Scope dilewati bila tidak ada user yang login (CLI/artisan/queue) agar seeder
 * dan Artisan tetap bisa bekerja di luar konteks HTTP.
 */
trait ScopedToWedding
{
    public static function bootScopedToWedding(): void
    {
        static::addGlobalScope('wedding', function (Builder $builder) {
            $weddingId = Wedding::current()?->id;

            if ($weddingId !== null) {
                $builder->where($builder->getModel()->qualifyColumn('wedding_id'), $weddingId);
            }
        });
    }
}