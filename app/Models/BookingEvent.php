<?php

namespace App\Models;

use App\Enums\BookingEventType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'hotel_id',
    'booking_id',
    'session_id',
    'type',
    'step',
    'value',
    'currency',
    'payload',
    'attribution',
    'user_agent',
    'ip_hash',
])]
class BookingEvent extends Model
{
    protected function casts(): array
    {
        return [
            'type' => BookingEventType::class,
            'value' => 'decimal:2',
            'payload' => 'array',
            'attribution' => 'array',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
