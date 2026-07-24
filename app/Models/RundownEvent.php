<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RundownEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'time',
        'activity',
        'pic',
        'location',
        'notes',
        'sort_order',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
