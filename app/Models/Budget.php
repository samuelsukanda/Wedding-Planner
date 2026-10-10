<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;
    use ScopedToWedding;

    protected $fillable = [
        'wedding_id',
        'vendor_id',
        'souvenir_id',
        'category',
        'item_name',
        'planned_budget',
        'actual_cost',
        'invoice',
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
     * Terisi hanya untuk baris yang auto-post dari modul Seserahan.
     */
    public function souvenir()
    {
        return $this->belongsTo(Souvenir::class);
    }
}
