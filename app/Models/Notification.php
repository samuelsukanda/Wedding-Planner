<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'title',
        'message',
        'type',
        'is_read',
        'reminder_date',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'reminder_date' => 'date',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
