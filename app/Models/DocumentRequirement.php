<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PRD Module 15: Daftar dokumen Persyaratan Nikah untuk satu pasangan.
 *
 * Dibuat dari salinan template, lalu punya PIC, deadline, status, dan
 * riwayat sendiri.
 */
class DocumentRequirement extends Model
{
    use HasFactory;
    use ScopedToWedding;

    public const STATUS_DEFAULT = 'Belum Lengkap';

    /** Pengingat dibuat HARI ini sebelum deadline. */
    public const REMINDER_WINDOW_DAYS = 7;

    protected $fillable = [
        'wedding_id',
        'document_template_id',
        'name',
        'pic_name',
        'phone',
        'deadline',
        'status',
        'document_file',
        'notes',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $item) {
            $item->status ??= self::STATUS_DEFAULT;
        });

        // Aturan model, bukan controller: riwayat dan pengingat selalu
        // ikut tersinkron di setiap jalur penyimpanan.
        static::saved(function (self $item) {
            $item->syncReminder();
        });
    }

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function histories()
    {
        return $this->hasMany(DocumentHistory::class);
    }

    public function isComplete(): bool
    {
        return $this->status === 'Lengkap';
    }

    public function isOverdue(): bool
    {
        return $this->deadline !== null
            && ! $this->isComplete()
            && $this->deadline->isPast();
    }

    /**
     * Deadline yang sudah lewat dan belum lengkap, untuk badge di UI.
     */
    public function statusLabel(): string
    {
        if ($this->isComplete()) {
            return 'Lengkap';
        }

        return $this->isOverdue() ? 'Terlambat' : $this->status;
    }

    /**
     * Deadline di dalam jendela pengingat (7 hari ke depan).
     */
    public function isDueSoon(): bool
    {
        if (! $this->deadline || $this->isComplete()) {
            return false;
        }

        return $this->deadline->between(
            now()->startOfDay(),
            now()->addDays(self::REMINDER_WINDOW_DAYS)->endOfDay()
        );
    }

    /**
     * Catat perubahan status ke riwayat.
     */
    public function logHistory(?string $note = null): void
    {
        $this->histories()->create([
            'wedding_id' => $this->wedding_id,
            'status' => $this->status,
            'pic_name' => $this->pic_name,
            'deadline' => $this->deadline,
            'note' => $note,
        ]);
    }

    /**
     * PRD: pengingat item deadline.
     *
     * Idempotent lewat updateOrCreate dengan kunci (wedding_id, type,
     * reminder_date). Item yang lewat deadline juga dibersihkan supaya
     * notifikasi basi tidak menumpuk.
     */
    public function syncReminder(): void
    {
        $type = 'dokumen_deadline';

        $existing = Notification::where('type', $type)
            ->where('title', $this->name)
            ->first();

        if (! $this->isDueSoon() && ! $this->isOverdue()) {
            $existing?->delete();

            return;
        }

        $label = $this->isOverdue() ? 'terlambat' : 'jatuh tempo';

        Notification::updateOrCreate(
            [
                'wedding_id' => $this->wedding_id,
                'type' => $type,
                'reminder_date' => $this->deadline,
            ],
            [
                'title' => $this->name,
                'message' => "Dokumen \"{$this->name}\" {$label} pada " . $this->deadline->format('d M Y') . '.',
                'is_read' => false,
            ]
        );
    }
}