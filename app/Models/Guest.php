<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'name',
        'address',
        'phone',
        'category',
        'attendance_status',
        'guest_count',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
