<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropdownOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_key',
        'group_name',
        'option_value',
        'meta_value',
        'sort_order',
    ];

    /**
     * Helper to get list of option values for a group_key, falling back to default array if empty.
     */
    public static function getOptions(string $groupKey, array $defaultOptions = []): array
    {
        try {
            $options = static::where('group_key', $groupKey)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->pluck('option_value')
                ->toArray();

            return !empty($options) ? $options : $defaultOptions;
        } catch (\Exception $e) {
            return $defaultOptions;
        }
    }

    /**
     * Predefined list of all configurable dropdown groups in the system.
     */
    public static function getGroups(): array
    {
        return [
            'checklist_category' => 'Kategori Checklist',
            'checklist_priority' => 'Prioritas Checklist',
            'checklist_status' => 'Status Checklist',
            'budget_category' => 'Kategori Budget',
            'vendor_category' => 'Kategori Vendor',
            'vendor_status' => 'Status Booking Vendor',
            'guest_category' => 'Kategori Tamu',
            'guest_status' => 'Status Kehadiran (RSVP)',
            'guest_title' => 'Gelar Tamu',
            'moodboard_category' => 'Kategori Moodboard',
            'contract_status' => 'Status Kontrak Vendor',
            'payment_status' => 'Status Pembayaran',
            'gift_type' => 'Jenis Hadiah',
        ];
    }
}
