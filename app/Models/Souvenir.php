<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PRD Module 14: Daftar Seserahan.
 *
 * Harga item yang sudah "Diterima" diposting ke Budget Planner sebagai
 * pengeluaran, supaya total anggaran dan laporan ikut memotongnya.
 */
class Souvenir extends Model
{
    use HasFactory;
    use ScopedToWedding;

    public const STATUS_RECEIVED = 'Diterima';

    protected $fillable = [
        'wedding_id',
        'vendor_id',
        'name',
        'link',
        'photo',
        'planned_price',
        'actual_cost',
        'status',
        'received_date',
        'notes',
    ];

    protected $casts = [
        'planned_price' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'received_date' => 'date',
    ];

    protected static function booted(): void
    {
        // Kolom NOT NULL di DB sudah punya default, tapi insert langsung lewat
        // model (mis. di seeder atau test) tidak mengambil default itu. Diisi
        // di sini supaya semua jalur konsisten dan auto-post budget tidak gagal.
        static::creating(function (self $souvenir) {
            $souvenir->planned_price ??= 0;
            $souvenir->actual_cost ??= 0;
            $souvenir->status ??= 'Belum Dipilih';
        });

        // Aturan model, bukan hanya di controller: setiap jalur penyimpanan
        // (create, update, mass assign) selalu menyelaraskan Budget Planner.
        static::saved(function (self $souvenir) {
            $souvenir->syncBudget();
        });
    }

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function budget()
    {
        return $this->hasOne(Budget::class);
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    /**
     * Selisih anggaran vs harga beli (positif = lebih murah).
     */
    public function variance(): float
    {
        return (float) $this->planned_price - (float) $this->actual_cost;
    }

    /**
     * Sinkronkan baris Budget Planner milik item ini.
     *
     * Idempotent: memakai updateOrCreate pada souvenir_id, jadi status yang
     * diubah berulang tidak akan menggandakan baris anggaran. Kalau item
     * belum "Diterima", baris budgetnya dihapus supaya angkatotal tidak
     * ikut menyimpan biaya yang belum jadi pengeluaran.
     */
    public function syncBudget(): void
    {
        $budget = $this->budget()->first();

        if (! $this->isReceived()) {
            $budget?->delete();

            return;
        }

        $wedding = Wedding::current();

        Budget::updateOrCreate(
            ['souvenir_id' => $this->id],
            [
                'wedding_id' => $this->wedding_id ?? $wedding?->id,
                'vendor_id' => $this->vendor_id,
                'category' => 'Seserahan',
                'item_name' => $this->name,
                'planned_budget' => $this->planned_price,
                'actual_cost' => $this->actual_cost,
                'notes' => 'Otomatis dari modul Seserahan.',
            ]
        );
    }
}