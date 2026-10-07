<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moodboard extends Model
{
    use HasFactory;
    use ScopedToWedding;

    protected $fillable = [
        'wedding_id',
        'category',
        'title',
        'image',
        'description',
        'link_reference',
        'link_preview',
        'notes',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
