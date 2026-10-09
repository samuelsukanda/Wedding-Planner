<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PRD Module 13: Informasi acara Lamaran (tanggal, waktu, lokasi, tema).
 *
 * Satu acara per wedding (kolom wedding_id unik) sehingga tidak perlu
 * proposal_event_id di tabel anak — relasinya lewat hasOne.
 */
class ProposalEvent extends Model
{
    use HasFactory;
    use ScopedToWedding;

    protected $fillable = [
        'wedding_id',
        'event_date',
        'event_time',
        'location',
        'theme',
        'notes',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    // Hanya satu acara per wedding, jadi tabel anak menunjuk wedding_id
    // dan relasinya lewat hasMany(... , 'wedding_id').
    public function checklists()
    {
        return $this->hasMany(ProposalChecklist::class, 'wedding_id', 'wedding_id');
    }

    public function guests()
    {
        return $this->hasMany(ProposalGuest::class, 'wedding_id', 'wedding_id');
    }

    public function budgets()
    {
        return $this->hasMany(ProposalBudget::class, 'wedding_id', 'wedding_id');
    }

    /**
     * Vendor, rundown, dan dokumentasi Lamaran memakai tabel yang sama dengan
     * resepsi; proposal_event_id memisahkan keduanya.
     */
    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function rundownEvents()
    {
        return $this->hasMany(RundownEvent::class);
    }

    public function moodboards()
    {
        return $this->hasMany(Moodboard::class);
    }
}