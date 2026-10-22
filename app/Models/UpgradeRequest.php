<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Permintaan upgrade paket Pro lewat transfer manual.
 *
 * Tidak memakai ScopedToWedding: daftar request di panel admin harus melihat
 * semua pasangan, bukan cuma wedding milik user yang sedang login.
 */
class UpgradeRequest extends Model
{
    protected $fillable = [
        'wedding_id',
        'user_id',
        'plan',
        'feature',
        'amount',
        'proof_path',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}