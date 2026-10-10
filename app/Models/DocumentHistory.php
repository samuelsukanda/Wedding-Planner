<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Model;

/**
 * PRD Module 15: riwayat perubahan satu dokumen Persyaratan Nikah.
 */
class DocumentHistory extends Model
{
    use ScopedToWedding;

    protected $fillable = [
        'document_requirement_id',
        'wedding_id',
        'status',
        'pic_name',
        'deadline',
        'note',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function requirement()
    {
        return $this->belongsTo(DocumentRequirement::class);
    }
}