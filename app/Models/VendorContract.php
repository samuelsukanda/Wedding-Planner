<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorContract extends Model
{
    use HasFactory;
    use ScopedToWedding;

    protected $fillable = [
        'wedding_id',
        'vendor_id',
        'contract_number',
        'nominal',
        'dp_amount',
        'final_amount',
        'due_date',
        'status',
        'contract_file',
        'notes',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'dp_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
