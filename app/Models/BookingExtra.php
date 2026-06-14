<?php

namespace App\Models;

use App\Enums\ExtraPricingUnit;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'hotel_id',
    'external_id',
    'name',
    'slug',
    'description',
    'price',
    'pricing_unit',
    'sort',
    'is_active',
])]
class BookingExtra extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'pricing_unit' => ExtraPricingUnit::class,
            'is_active' => 'boolean',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
