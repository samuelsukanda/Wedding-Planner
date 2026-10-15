<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Setelan tingkat sistem (harga Pro, rekening tujuan, nomor WA admin).
 *
 * Sengaja tanpa ScopedToWedding: nilainya global untuk semua pasangan, bukan
 * milik satu pasangan. Semua akses ke tabel ini hanya lewat get() dan put().
 */
class Setting extends Model
{
    // Nama kelas "Setting" akan di-pluralisasi jadi "settings", sedangkan
    // tabelnya sengaja app_settings supaya tidak clash dengan setelan lain.
    protected $table = 'app_settings';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}