<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsGoal extends Model
{
    use HasFactory;
    use ScopedToWedding;

    public const TYPE_SETORAN = 'setoran';
    public const TYPE_PENARIKAN = 'penarikan';

    protected $fillable = [
        'wedding_id',
        'name',
        'target_amount',
        'initial_balance',
        'current_balance',
        'periodic_amount',
        'frequency',
        'target_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'periodic_amount' => 'decimal:2',
        'target_date' => 'date',
    ];

    protected static function booted(): void
    {
        // Saat goal dibuat belum ada transaksi, jadi saldo saat ini PASTI sama
        // dengan saldo awal. Menjadikannya aturan model (bukan hanya di
        // controller) menjaga semua jalur pembuatan tetap konsisten.
        static::creating(function (self $goal) {
            $goal->current_balance = $goal->initial_balance ?? 0;
        });
    }

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function transactions()
    {
        return $this->hasMany(SavingsTransaction::class);
    }

    /**
     * PRD Business Rule 2:
     * saldo = saldo awal + setoran - penarikan
     *
     * Ini satu-satunya tempat yang menulis current_balance.
     */
    public function recalculate(): void
    {
        $totals = $this->transactions()
            ->selectRaw("
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) AS setoran,
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) AS penarikan
            ", [self::TYPE_SETORAN, self::TYPE_PENARIKAN])
            ->first();

        $balance = (float) $this->initial_balance
            + (float) ($totals->setoran ?? 0)
            - (float) ($totals->penarikan ?? 0);

        // Target tercapai -> status otomatis "Tercapai" (PRD: Target tanggal tercapai).
        $status = $this->status;
        if ($status !== 'Ditunda' && $balance >= (float) $this->target_amount && (float) $this->target_amount > 0) {
            $status = 'Tercapai';
        } elseif ($status === 'Tercapai' && $balance < (float) $this->target_amount) {
            $status = 'Aktif';
        }

        $this->forceFill(['current_balance' => $balance, 'status' => $status])->save();
    }

    /**
     * PRD Fitur: Progress target (%). Dibatasi 100 supaya bar tidak melebihi container.
     */
    public function progressPercent(): float
    {
        $target = (float) $this->target_amount;

        if ($target <= 0) {
            return 0.0;
        }

        return round(min(100, ((float) $this->current_balance / $target) * 100), 1);
    }

    /**
     * PRD Fitur: Estimasi kekurangan dana.
     */
    public function shortfall(): float
    {
        return max(0, (float) $this->target_amount - (float) $this->current_balance);
    }

    /**
     * PRD Fitur: Setoran manual dan rutin.
     *
     * Jatuh tempo dihitung dari setoran terakhir + frekuensi. Transaksi tetap
     * dicatat manual supaya saldo tidak pernah berubah tanpa Aksi user.
     */
    public function nextDueDate(): ?string
    {
        if (! $this->frequency || (float) $this->periodic_amount <= 0) {
            return null;
        }

        $last = $this->transactions()
            ->where('type', self::TYPE_SETORAN)
            ->orderByDesc('transaction_date')
            ->value('transaction_date');

        $base = $last ? \Carbon\Carbon::parse($last) : \Carbon\Carbon::today();

        $next = match ($this->frequency) {
            'Mingguan' => $base->copy()->addWeek(),
            'Bulanan' => $base->copy()->addMonth(),
            'Tahunan' => $base->copy()->addYear(),
            default => null,
        };

        return $next?->toDateString();
    }

    /**
     * Saldo awal dikunci begitu ada transaksi, supaya rumus saldo tetap
     * konsisten dengan riwayatnya.
     */
    public function hasTransactions(): bool
    {
        return $this->transactions()->exists();
    }
}