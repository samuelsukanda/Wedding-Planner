<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Setelan tingkat sistem (harga Pro, rekening tujuan transfer).
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

    /**
     * Daftar rekening tujuan transfer Pro.
     *
     * Disimpan sebagai JSON di kunci pro_banks. Data lama masih berupa teks
     * satu rekening per baris di kunci pro_bank; itu dibaca sebagai cadangan
     * supaya halaman pembayaran tidak pernah kosong sebelum superadmin sempat
     * mengisi ulang lewat halaman Pengaturan Pro.
     *
     * @return list<array{icon: string, name: string, number: string}>
     */
    public static function proBanks(): array
    {
        $rows = json_decode((string) static::get('pro_banks', ''), true);

        if (is_array($rows)) {
            return array_values(array_filter(
                $rows,
                fn ($row) => is_array($row) && trim((string) ($row['number'] ?? '')) !== ''
            ));
        }

        // shortcut: rekening lama "BCA - 1234567890" tetap terbaca sebagai
        // nama + nomor supaya tidak perlu diketik ulang di Pengaturan Pro.
        // Hapus cabang ini setelah semua data pindah ke pro_banks.
        $lines = preg_split('/\R/', trim((string) static::get('pro_bank', ''))) ?: [];

        return array_values(array_map(function (string $line) {
            // Format lama: "BANK - NOMOR". Tanpa pemisah, seluruh baris
            // dianggap nomor supaya tidak ada teks yang hilang.
            $parts = array_map('trim', explode(' - ', trim($line), 2));

            return [
                'icon' => 'fa-building-columns',
                'name' => count($parts) === 2 ? $parts[0] : '',
                'number' => count($parts) === 2 ? $parts[1] : $parts[0],
            ];
        }, array_values(array_filter($lines, fn (string $line) => trim($line) !== ''))));
    }
}