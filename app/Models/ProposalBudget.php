<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PRD Module 13: Budget Lamaran.
 *
 * Business Rule 4 — budget lamaran dipisahkan dari budget resepsi, tapi
 * tetap masuk ke ringkasan total biaya persiapan. Karena itu tabel terpisah,
 * bukan baris di `budgets`.
 */
class ProposalBudget extends Model
{
    use HasFactory;
    use ScopedToWedding;

    protected $fillable = [
        'wedding_id',
        'vendor_id',
        'category',
        'item_name',
        'planned_budget',
        'actual_cost',
        'notes',
    ];

    protected $casts = [
        'planned_budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Selisih rencana vs realisasi (positif = hemat).
     */
    public function variance(): float
    {
        return (float) $this->planned_budget - (float) $this->actual_cost;
    }

    public function isOverBudget(): bool
    {
        return (float) $this->actual_cost > (float) $this->planned_budget;
    }
}