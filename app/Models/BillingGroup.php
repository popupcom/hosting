<?php

namespace App\Models;

use App\Enums\BillingGroupStatus;
use App\Enums\ServiceCatalogBillingInterval;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'project_id',
    'name',
    'billing_interval',
    'status',
    'notes',
])]
class BillingGroup extends Model
{
    protected function casts(): array
    {
        return [
            'status' => BillingGroupStatus::class,
            'billing_interval' => ServiceCatalogBillingInterval::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillingGroupItem::class);
    }

    public function projectServices(): HasMany
    {
        return $this->hasMany(ProjectService::class);
    }
}
