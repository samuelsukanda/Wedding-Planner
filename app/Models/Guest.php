<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;
    use ScopedToWedding;

    protected $fillable = [
        'wedding_id',
        'title',
        'name',
        'address',
        'phone',
        'category',
        'attendance_status',
        'guest_count',
        'wa_sent',
    ];

    protected $casts = [
        'wa_sent' => 'boolean',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function setPhoneAttribute($value)
    {
        if (!$value) {
            $this->attributes['phone'] = $value;
            return;
        }

        $digits = preg_replace('/[^0-9]/', '', $value);
        if (str_starts_with($digits, '62') && strlen($digits) > 2) {
            $digits = '0' . substr($digits, 2);
        }

        $this->attributes['phone'] = $digits;
    }
}
