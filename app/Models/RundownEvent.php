<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RundownEvent extends Model
{
    use HasFactory;
    use ScopedToWedding;

    protected $fillable = [
        'wedding_id',
        'time',
        'activity',
        'pic',
        'location',
        'notes',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
