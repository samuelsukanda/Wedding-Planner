<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'title',
        'category',
        'deadline',
        'priority',
        'status',
        'description',
        'attachment',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
