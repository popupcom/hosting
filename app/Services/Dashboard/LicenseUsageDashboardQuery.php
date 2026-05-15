<?php

namespace App\Services\Dashboard;

use App\Enums\LicenseAssignmentStatus;
use App\Enums\LicenseSharingModel;
use App\Models\LicenseProduct;
use App\Models\ProjectLicenseAssignment;
use Illuminate\Database\Eloquent\Builder;

final class LicenseUsageDashboardQuery
{
    public static function countActiveAssignments(): int
    {
        return ProjectLicenseAssignment::query()
            ->where('status', LicenseAssignmentStatus::Active)
            ->count();
    }

    public static function countPendingCancellation(): int
    {
        return ProjectLicenseAssignment::query()
            ->where('status', LicenseAssignmentStatus::PendingCancellation)
            ->count();
    }

    public static function countSharedProducts(): int
    {
        return LicenseProduct::query()
            ->activeCatalog()
            ->where('license_model', LicenseSharingModel::Shared)
            ->count();
    }

    public static function countDedicatedProducts(): int
    {
        return LicenseProduct::query()
            ->activeCatalog()
            ->whereIn('license_model', [
                LicenseSharingModel::Dedicated,
                LicenseSharingModel::SeatBased,
            ])
            ->count();
    }

    public static function countFullyUtilizedProducts(): int
    {
        return LicenseProduct::query()
            ->activeCatalog()
            ->get()
            ->filter(fn (LicenseProduct $product): bool => $product->isFullyUtilized())
            ->count();
    }

    public static function countHighUtilizationProducts(float $thresholdPercent = 80): int
    {
        return LicenseProduct::query()
            ->activeCatalog()
            ->get()
            ->filter(function (LicenseProduct $product) use ($thresholdPercent): bool {
                $percent = $product->utilizationPercent();

                return $percent !== null && $percent >= $thresholdPercent;
            })
            ->count();
    }

    public static function assignmentsEndingSoonQuery(int $days = 60): Builder
    {
        $until = now()->addDays($days)->toDateString();

        return ProjectLicenseAssignment::query()
            ->with(['licenseProduct', 'project.client'])
            ->whereIn('status', [
                LicenseAssignmentStatus::Active,
                LicenseAssignmentStatus::PendingCancellation,
            ])
            ->whereNotNull('cancellation_effective_date')
            ->whereDate('cancellation_effective_date', '<=', $until)
            ->orderBy('cancellation_effective_date');
    }

    public static function highUtilizationProductsQuery(float $thresholdPercent = 80): Builder
    {
        return LicenseProduct::query()
            ->activeCatalog()
            ->withCount([
                'assignments as used_count' => fn (Builder $q) => $q->countsAsUsed(),
            ])
            ->where('total_available_licenses', '>', 0)
            ->havingRaw('(used_count * 100.0 / total_available_licenses) >= ?', [$thresholdPercent])
            ->orderByDesc('used_count');
    }

    public static function sharedProductsQuery(): Builder
    {
        return LicenseProduct::query()
            ->activeCatalog()
            ->where('license_model', LicenseSharingModel::Shared)
            ->withCount([
                'assignments as used_count' => fn (Builder $q) => $q->countsAsUsed(),
            ])
            ->orderBy('name');
    }

    public static function dedicatedProductsQuery(): Builder
    {
        return LicenseProduct::query()
            ->activeCatalog()
            ->whereIn('license_model', [
                LicenseSharingModel::Dedicated,
                LicenseSharingModel::SeatBased,
            ])
            ->withCount([
                'assignments as used_count' => fn (Builder $q) => $q->countsAsUsed(),
            ])
            ->orderBy('name');
    }
}
