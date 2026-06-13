<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'hotel_id',
    'external_id',
    'category',
    'name',
    'slug',
    'max_pax',
    'size',
    'rooms',
    'description',
    'image_url',
    'price_per_night',
    'badge',
    'sort',
    'is_active',
])]
class BookingUnit extends Model
{
    protected function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
