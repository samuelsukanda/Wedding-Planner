<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'vendor_id',
        'nominal',
        'payment_date',
        'payment_method',
        'status',
        'invoice_file',
        'reminder_date',
        'notes',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'payment_date' => 'date',
        'reminder_date' => 'date',
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
