<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PRD Module 13: Checklist persiapan Lamaran.
 * Contoh item PRD: tanggal, venue, cincin, pakaian, dekorasi, makeup,
 * fotografer, konsumsi, seserahan lamaran, undangan, dokumentasi, rundown.
 */
class ProposalChecklist extends Model
{
    use HasFactory;
    use ScopedToWedding;

    protected $fillable = [
        'wedding_id',
        'title',
        'category',
        'deadline',
        'status',
        'priority',
        'notes',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function isDone(): bool
    {
        return $this->status === 'Done';
    }

    /**
     * Deadline yang sudah lewat dan belum selesai.
     */
    public function isOverdue(): bool
    {
        return $this->deadline !== null
            && ! $this->isDone()
            && $this->deadline->isPast();
    }

    public function statusLabel(): string
    {
        if ($this->isDone()) {
            return 'Done';
        }

        return $this->isOverdue() ? 'Terlambat' : ($this->status ?: 'Todo');
    }
}