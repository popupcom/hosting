<?php

namespace App\Models;

use App\Enums\ProjectServiceMocoSyncStatus;
use App\Enums\ProjectServiceStatus;
use App\Enums\ServiceCatalogBillingInterval;
use App\Services\ProjectServices\ProjectServiceSnapshotter;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[Fillable([
    'project_id',
    'service_catalog_item_id',
    'name_snapshot',
    'description_snapshot',
    'cost_price_snapshot',
    'sales_price_snapshot',
    'billing_interval_snapshot',
    'custom_name',
    'custom_description',
    'quantity',
    'custom_quantity',
    'custom_cost_price',
    'custom_sales_price',
    'custom_billing_interval',
    'start_date',
    'end_date',
    'minimum_term_months',
    'next_renewal_date',
    'cancellation_notice_until',
    'cancellation_date',
    'cancelled_at',
    'cancellation_reason',
    'renews_automatically',
    'do_not_renew',
    'billing_group_id',
    'status',
    'moco_sync_status',
    'notes',
    'moco_invoice_reference',
    'price_change_effective_from',
    'created_by',
    'updated_by',
])]
class ProjectService extends Model
{
    protected static function booted(): void
    {
        static::creating(function (ProjectService $service): void {
            if ($service->created_by === null && Auth::id()) {
                $service->created_by = Auth::id();
            }
            if (blank($service->name_snapshot) && $service->service_catalog_item_id) {
                $service->loadMissing('serviceCatalogItem');
                ProjectServiceSnapshotter::applyCatalogSnapshots($service);
            }
        });

        static::saving(function (ProjectService $service): void {
            if (Auth::id()) {
                $service->updated_by = Auth::id();
            }

            if ($service->do_not_renew) {
                $service->renews_automatically = false;
            }

            if (
                $service->end_date !== null
                && $service->end_date->lte(now()->startOfDay())
                && in_array($service->status, [ProjectServiceStatus::Active, ProjectServiceStatus::PendingCancellation], true)
            ) {
                $service->status = ProjectServiceStatus::Expired;
            }
        });

        static::saved(function (ProjectService $service): void {
            if ($service->wasChanged('billing_group_id') || $service->wasRecentlyCreated) {
                ProjectServiceSnapshotter::syncBillingGroupItem($service);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => ProjectServiceStatus::class,
            'moco_sync_status' => ProjectServiceMocoSyncStatus::class,
            'custom_billing_interval' => ServiceCatalogBillingInterval::class,
            'billing_interval_snapshot' => ServiceCatalogBillingInterval::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'next_renewal_date' => 'date',
            'cancellation_notice_until' => 'date',
            'cancellation_date' => 'date',
            'cancelled_at' => 'datetime',
            'price_change_effective_from' => 'date',
            'quantity' => 'decimal:4',
            'custom_quantity' => 'decimal:4',
            'custom_cost_price' => 'decimal:2',
            'custom_sales_price' => 'decimal:2',
            'cost_price_snapshot' => 'decimal:2',
            'sales_price_snapshot' => 'decimal:2',
            'renews_automatically' => 'boolean',
            'do_not_renew' => 'boolean',
            'minimum_term_months' => 'integer',
        ];
    }

    public function getEffectiveQuantityAttribute(): string
    {
        if ($this->custom_quantity !== null) {
            return (string) $this->custom_quantity;
        }

        return (string) ($this->quantity ?? 1);
    }

    public function getEffectiveNameAttribute(): string
    {
        if (filled($this->custom_name)) {
            return (string) $this->custom_name;
        }
        if (filled($this->name_snapshot)) {
            return (string) $this->name_snapshot;
        }

        return (string) ($this->serviceCatalogItem?->name ?? '—');
    }

    public function getEffectiveDescriptionAttribute(): ?string
    {
        if (filled($this->custom_description)) {
            return (string) $this->custom_description;
        }
        if (filled($this->description_snapshot)) {
            return (string) $this->description_snapshot;
        }

        return $this->serviceCatalogItem?->description;
    }

    public function getEffectiveCostPriceAttribute(): ?string
    {
        if ($this->custom_cost_price !== null) {
            return (string) $this->custom_cost_price;
        }
        if ($this->cost_price_snapshot !== null) {
            return (string) $this->cost_price_snapshot;
        }

        return null;
    }

    public function getEffectiveSalesPriceAttribute(): ?string
    {
        if ($this->custom_sales_price !== null) {
            return (string) $this->custom_sales_price;
        }
        if ($this->sales_price_snapshot !== null) {
            return (string) $this->sales_price_snapshot;
        }

        return null;
    }

    public function getEffectiveBillingIntervalAttribute(): ?ServiceCatalogBillingInterval
    {
        if ($this->custom_billing_interval !== null) {
            return $this->custom_billing_interval;
        }
        if ($this->billing_interval_snapshot !== null) {
            return $this->billing_interval_snapshot instanceof ServiceCatalogBillingInterval
                ? $this->billing_interval_snapshot
                : ServiceCatalogBillingInterval::tryFrom((string) $this->billing_interval_snapshot);
        }

        return null;
    }

    public function getMarginAmountAttribute(): ?float
    {
        $ek = $this->effective_cost_price;
        $vk = $this->effective_sales_price;
        if ($ek === null || $vk === null) {
            return null;
        }

        return ((float) $vk - (float) $ek) * (float) $this->effective_quantity;
    }

    public function getMarginPercentageAttribute(): ?float
    {
        $ek = $this->effective_cost_price;
        if ($ek === null || (float) $ek <= 0) {
            return null;
        }
        $m = $this->margin_amount;
        if ($m === null) {
            return null;
        }

        return ($m / ((float) $ek * (float) $this->effective_quantity)) * 100.0;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function serviceCatalogItem(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalogItem::class);
    }

    public function billingGroup(): BelongsTo
    {
        return $this->belongsTo(BillingGroup::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProjectServiceStatus::Active);
    }

    public function scopeEndingSoon(Builder $query, int $withinDays = 60): Builder
    {
        return $query
            ->whereIn('status', [
                ProjectServiceStatus::Active->value,
                ProjectServiceStatus::PendingCancellation->value,
            ])
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays($withinDays)->toDateString()]);
    }

    public function scopeDoNotRenew(Builder $query): Builder
    {
        return $query->where('do_not_renew', true);
    }

    public function scopeWithoutBillingGroup(Builder $query): Builder
    {
        return $query->whereNull('billing_group_id');
    }
}
