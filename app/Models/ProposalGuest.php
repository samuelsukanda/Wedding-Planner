<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PRD Module 13: Daftar keluarga/tamu Lamaran.
 * Sengaja tabel terpisah dari `guests` (tamu resepsi) karena keduanya punya
 * daftar dan status kehadiran yang berbeda.
 */
class ProposalGuest extends Model
{
    use HasFactory;
    use ScopedToWedding;

    protected $fillable = [
        'wedding_id',
        'name',
        'title',
        'relation',
        'category',
        'phone',
        'guest_count',
        'attendance_status',
        'notes',
    ];

    protected $casts = [
        'guest_count' => 'integer',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}