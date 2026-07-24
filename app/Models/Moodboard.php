<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moodboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'category',
        'title',
        'image',
        'description',
        'link_reference',
        'notes',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
