<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'hotel_id',
    'key',
    'label',
    'sublabel',
    'discount_percent',
    'deposit_percent',
    'rules',
    'is_default',
    'sort',
    'is_active',
])]
class BookingRate extends Model
{
    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'deposit_percent' => 'decimal:2',
            'rules' => 'array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
