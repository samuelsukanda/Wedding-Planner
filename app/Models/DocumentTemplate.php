<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PRD Module 15: Template dokumen Persyaratan Nikah yang dikelola superadmin.
 *
 * Daftar dokumen disimpan sebagai JSON di kolom items supaya template cukup
 * satu baris, bukan tabel terpisah. Item tiap pasangan tetap baris penuh di
 * document_requirements supaya bisa punya PIC, deadline, dan status sendiri.
 *
 * Sengaja tanpa ScopedToWedding: template bersifat sistem, bukan milik satu
 * pasangan, jadi harus bisa dilihat dan diubah superadmin yang tidak punya
 * wedding.
 */
class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'items',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'items' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Salin template ini ke daftar syarat satu pasangan.
     *
     * Dipakai superadmin saat membuat data pernikahan baru, dan bisa dipanggil
     * ulang oleh pasangan sendiri untuk menambah dokumen yang terlewat.
     *
     * @return int jumlah dokumen yang tersalin
     */
    public function copyToWedding(Wedding $wedding): int
    {
        $created = 0;

        foreach ($this->items ?? [] as $item) {
            DocumentRequirement::create([
                'wedding_id' => $wedding->id,
                'document_template_id' => $this->id,
                'name' => $item['name'] ?? null,
                'status' => $item['status'] ?? DocumentRequirement::STATUS_DEFAULT,
            ]);

            $created++;
        }

        return $created;
    }
}