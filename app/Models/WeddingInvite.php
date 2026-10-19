<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tautan undangan agar pasangan bergabung ke Wedding Kit yang sama.
 *
 * Sengaja tanpa ScopedToWedding: yang dicari lewat kueri adalah token, bukan
 * data milik pasangan tertentu, jadi tidak perlu scope. Tautan itu sendiri
 * sudah terikat ke satu wedding lewat kolom wedding_id.
 */
class WeddingInvite extends Model
{
    protected $fillable = [
        'wedding_id',
        'token',
        'expires_at',
        'accepted_at',
        'accepted_user_id',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public const VALID_DAYS = 7;

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acceptedUser()
    {
        return $this->belongsTo(User::class, 'accepted_user_id');
    }

    /** Undangan yang masih bisa dipakai: belum dipakai dan belum kedaluwarsa. */
    public function isPending(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }
}