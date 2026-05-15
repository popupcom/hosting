<?php

namespace App\Models;

use App\Enums\LicenseAssignmentStatus;
use App\Enums\LicenseProductStatus;
use App\Enums\LicenseSharingModel;
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
    'notes',
    'status',
])]
class LicenseProduct extends Model
{
    protected function casts(): array
    {
        return [
            'license_model' => LicenseSharingModel::class,
            'status' => LicenseProductStatus::class,
            'total_available_licenses' => 'integer',
        ];
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
