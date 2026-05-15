<?php

namespace App\Models;

use App\Enums\SupportPackageStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_id',
    'name',
    'scope_of_services',
    'response_time',
    'update_interval',
    'price',
    'billing_interval',
    'starts_at',
    'cancellation_notice_days',
    'status',
    'notes',
])]
class SupportPackage extends Model
{
    protected function casts(): array
    {
        return [
            'status' => SupportPackageStatus::class,
            'starts_at' => 'date',
            'price' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SupportPackageStatus::Active);
    }
}
