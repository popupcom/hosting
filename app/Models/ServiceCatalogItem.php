<?php

namespace App\Models;

use App\Enums\ServiceCatalogBillingInterval;
use App\Enums\ServiceCatalogCategory;
use App\Enums\ServiceCatalogUnit;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'category',
    'description',
    'unit',
    'default_quantity',
    'cost_price',
    'sales_price',
    'billing_interval',
    'minimum_term_months',
    'is_active',
    'sort_order',
    'moco_article_id',
    'notes',
])]
class ServiceCatalogItem extends Model
{
    protected function casts(): array
    {
        return [
            'category' => ServiceCatalogCategory::class,
            'unit' => ServiceCatalogUnit::class,
            'billing_interval' => ServiceCatalogBillingInterval::class,
            'default_quantity' => 'decimal:4',
            'cost_price' => 'decimal:2',
            'sales_price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'minimum_term_months' => 'integer',
        ];
    }

    public function projectServices(): HasMany
    {
        return $this->hasMany(ProjectService::class);
    }

    public function scopeActiveCatalog(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getMarginAmountAttribute(): ?float
    {
        if ($this->cost_price === null || $this->sales_price === null) {
            return null;
        }

        return (float) $this->sales_price - (float) $this->cost_price;
    }

    public function getMarginPercentageAttribute(): ?float
    {
        $ek = $this->cost_price;
        if ($ek === null || (float) $ek <= 0) {
            return null;
        }
        $m = $this->margin_amount;
        if ($m === null) {
            return null;
        }

        return ($m / (float) $ek) * 100.0;
    }
}
