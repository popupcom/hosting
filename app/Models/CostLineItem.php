<?php

namespace App\Models;

use App\Enums\CostLineItemType;
use App\Enums\MocoSyncStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CostLineItem extends Model
{
    use Concerns\HasIntegrationSyncStates;

    #[Fillable([
        'client_id',
        'project_id',
        'line_type',
        'billable_type',
        'billable_id',
        'cost_price',
        'selling_price',
        'billing_interval',
        'moco_sync_status',
        'is_active',
        'notes',
    ])]
    protected function casts(): array
    {
        return [
            'line_type' => CostLineItemType::class,
            'moco_sync_status' => MocoSyncStatus::class,
            'is_active' => 'boolean',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePendingMocoSync(Builder $query): Builder
    {
        return $query->where('moco_sync_status', MocoSyncStatus::Pending);
    }
}
