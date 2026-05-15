<?php

namespace App\Models;

use App\Enums\ServiceCatalogBillingInterval;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'service_catalog_item_id',
    'description',
    'included_services',
    'monthly_minutes',
    'yearly_hours',
    'update_frequency',
    'response_time',
    'minimum_term_months',
    'billing_interval',
    'bill_yearly_in_advance',
    'is_active',
    'sort_order',
    'notes',
    'includes_daily_backups',
    'includes_plugin_updates',
    'includes_link_monitoring',
    'includes_security_checks',
    'includes_uptime_monitoring',
    'includes_wordpress_core_update',
    'includes_theme_update',
    'includes_performance_check',
    'includes_multisite',
    'includes_custom_websites',
    'includes_online_shops',
])]
class SupportPackage extends Model
{
    protected static function booted(): void
    {
        static::saving(function (SupportPackage $package): void {
            if ($package->monthly_minutes !== null) {
                $package->yearly_hours = round(((float) $package->monthly_minutes * 12) / 60, 2);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'monthly_minutes' => 'decimal:2',
            'yearly_hours' => 'decimal:2',
            'minimum_term_months' => 'integer',
            'bill_yearly_in_advance' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'billing_interval' => ServiceCatalogBillingInterval::class,
            'includes_daily_backups' => 'boolean',
            'includes_plugin_updates' => 'boolean',
            'includes_link_monitoring' => 'boolean',
            'includes_security_checks' => 'boolean',
            'includes_uptime_monitoring' => 'boolean',
            'includes_wordpress_core_update' => 'boolean',
            'includes_theme_update' => 'boolean',
            'includes_performance_check' => 'boolean',
            'includes_multisite' => 'boolean',
            'includes_custom_websites' => 'boolean',
            'includes_online_shops' => 'boolean',
        ];
    }

    public function serviceCatalogItem(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalogItem::class);
    }

    public function projectAssignments(): HasMany
    {
        return $this->hasMany(ProjectSupportPackage::class);
    }

    public function scopeActiveCatalog(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /** @return Attribute<?string, never> */
    protected function monthlySalesPrice(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->serviceCatalogItem?->sales_price !== null
            ? (string) $this->serviceCatalogItem->sales_price
            : null);
    }

    /** @return Attribute<?string, never> */
    protected function yearlySalesPrice(): Attribute
    {
        return Attribute::get(function (): ?string {
            $monthly = $this->serviceCatalogItem?->sales_price;
            if ($monthly === null) {
                return null;
            }

            return number_format((float) $monthly * 12, 2, '.', '');
        });
    }

    /** @return Attribute<?string, never> */
    protected function costPrice(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->serviceCatalogItem?->cost_price !== null
            ? (string) $this->serviceCatalogItem->cost_price
            : null);
    }

    /** @return Attribute<?float, never> */
    protected function marginPercent(): Attribute
    {
        return Attribute::get(function (): ?float {
            $sales = $this->serviceCatalogItem?->sales_price;
            $cost = $this->serviceCatalogItem?->cost_price;
            if ($sales === null || $cost === null || (float) $sales <= 0) {
                return null;
            }

            return round((((float) $sales - (float) $cost) / (float) $sales) * 100, 1);
        });
    }

    /**
     * @return list<string>
     */
    public function includedFeatureLabels(): array
    {
        $map = [
            'includes_daily_backups' => 'Tägliche Backups',
            'includes_plugin_updates' => 'Plugin-Updates',
            'includes_link_monitoring' => 'Link-Monitoring',
            'includes_security_checks' => 'Security-Checks',
            'includes_uptime_monitoring' => 'Uptime-Monitoring',
            'includes_wordpress_core_update' => 'WordPress-Core-Update',
            'includes_theme_update' => 'Theme-Update',
            'includes_performance_check' => 'Performance-Check',
            'includes_multisite' => 'Multisite',
            'includes_custom_websites' => 'Custom Websites',
            'includes_online_shops' => 'Onlineshops',
        ];

        $labels = [];
        foreach ($map as $field => $label) {
            if ($this->{$field}) {
                $labels[] = $label;
            }
        }

        return $labels;
    }
}
