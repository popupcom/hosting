<?php

namespace App\Models;

use App\Enums\LicenseAssignmentStatus;
use App\Enums\LicenseProductStatus;
use App\Enums\LicenseSharingModel;
use App\Support\LicenseCodeRules;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'provider',
    'category',
    'total_available_licenses',
    'license_model',
    'shared_license_code',
    'requires_individual_license_code',
    'notes',
    'status',
])]
class LicenseProduct extends Model
{
    protected static function booted(): void
    {
        static::saving(function (LicenseProduct $product): void {
            if ($product->license_model === LicenseSharingModel::Dedicated) {
                $product->requires_individual_license_code = true;
            }

            if ($product->license_model === LicenseSharingModel::Shared && ! $product->requires_individual_license_code) {
                $product->shared_license_code = $product->shared_license_code ?: null;
            }

            LicenseCodeRules::validateProduct($product);
        });
    }

    protected function casts(): array
    {
        return [
            'license_model' => LicenseSharingModel::class,
            'status' => LicenseProductStatus::class,
            'total_available_licenses' => 'integer',
            'requires_individual_license_code' => 'boolean',
        ];
    }

    public function usesSharedLicenseCode(): bool
    {
        return LicenseCodeRules::productUsesSharedCode($this);
    }

    public function requiresAssignmentLicenseCode(): bool
    {
        return LicenseCodeRules::productRequiresAssignmentCode($this);
    }

    public function scopeSharedModel(Builder $query): Builder
    {
        return $query->where('license_model', LicenseSharingModel::Shared);
    }

    public function scopeDedicatedModel(Builder $query): Builder
    {
        return $query->whereIn('license_model', [
            LicenseSharingModel::Dedicated,
            LicenseSharingModel::SeatBased,
        ]);
    }

    /**
     * Lizenzprodukte mit Auslastung ≥ Schwellwert (SQLite: kein HAVING auf withCount-Alias).
     */
    public function scopeWhereUtilizationPercentAtLeast(Builder $query, float $thresholdPercent): Builder
    {
        $productTable = $query->getModel()->getTable();
        $assignmentTable = (new ProjectLicenseAssignment)->getTable();
        $prefix = $query->getConnection()->getTablePrefix();
        $p = $prefix.$productTable;
        $a = $prefix.$assignmentTable;

        return $query->whereRaw(
            '(SELECT COUNT(*) FROM '.$a.' WHERE '.$a.'.license_product_id = '.$p.'.id AND '.$a.'.status IN (?, ?)) * 100.0 / '.$p.'.total_available_licenses >= ?',
            [
                LicenseAssignmentStatus::Active->value,
                LicenseAssignmentStatus::PendingCancellation->value,
                $thresholdPercent,
            ]
        );
    }

    public function scopeHighUtilization(Builder $query, float $thresholdPercent = 80): Builder
    {
        return $query
            ->where('total_available_licenses', '>', 0)
            ->whereUtilizationPercentAtLeast($thresholdPercent)
            ->withCount([
                'assignments as used_count' => fn (Builder $q) => $q->countsAsUsed(),
            ]);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProjectLicenseAssignment::class);
    }

    /** @deprecated */
    public function projectLicenses(): HasMany
    {
        return $this->assignments();
    }

    public function scopeActiveCatalog(Builder $query): Builder
    {
        return $query->where('status', LicenseProductStatus::Active);
    }

    public function usedLicensesCount(): int
    {
        return $this->assignments()->countsAsUsed()->count();
    }

    public function freeLicensesCount(): int
    {
        $total = (int) $this->total_available_licenses;
        $used = $this->usedLicensesCount();

        return max(0, $total - $used);
    }

    public function utilizationPercent(): ?float
    {
        $total = (int) $this->total_available_licenses;
        if ($total <= 0) {
            return null;
        }

        return round(($this->usedLicensesCount() / $total) * 100, 1);
    }

    public function isFullyUtilized(): bool
    {
        $total = (int) $this->total_available_licenses;

        return $total > 0 && $this->freeLicensesCount() <= 0;
    }

    public function activeAssignmentsCount(): int
    {
        return $this->assignments()->where('status', LicenseAssignmentStatus::Active)->count();
    }
}
