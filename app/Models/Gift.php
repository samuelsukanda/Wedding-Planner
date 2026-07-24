<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'giver_name',
        'gift_type',
        'nominal',
        'description',
        'is_thank_you_sent',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'is_thank_you_sent' => 'boolean',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
