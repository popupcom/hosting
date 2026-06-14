<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'hotel_id',
    'reference',
    'status',
    'booking_unit_id',
    'booking_rate_id',
    'check_in',
    'check_out',
    'nights',
    'adults',
    'children',
    'child_ages',
    'board',
    'package',
    'extras',
    'insurance',
    'guest',
    'units_total',
    'extras_total',
    'insurance_total',
    'grand_total',
    'deposit_due',
    'currency',
    'payment_method',
    'payment_status',
    'payment_reference',
    'pms_reservation_id',
    'session_id',
    'locale',
    'attribution',
    'confirmed_at',
])]
class Booking extends Model
{
    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'check_in' => 'date',
            'check_out' => 'date',
            'child_ages' => 'array',
            'extras' => 'array',
            'insurance' => 'boolean',
            'guest' => 'array',
            'attribution' => 'array',
            'units_total' => 'decimal:2',
            'extras_total' => 'decimal:2',
            'insurance_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'deposit_due' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(BookingUnit::class, 'booking_unit_id');
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(BookingRate::class, 'booking_rate_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(BookingEvent::class);
    }
}
