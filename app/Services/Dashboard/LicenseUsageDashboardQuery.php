<?php

namespace App\Services\Dashboard;

use App\Enums\LicenseAssignmentStatus;
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

    public static function countFullyUtilizedProducts(): int
    {
        return LicenseProduct::query()
            ->activeCatalog()
            ->get()
            ->filter(fn (LicenseProduct $product): bool => $product->isFullyUtilized())
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

    public static function fullyUtilizedProductsQuery(): Builder
    {
        return LicenseProduct::query()
            ->activeCatalog()
            ->withCount([
                'assignments as used_count' => fn (Builder $q) => $q->countsAsUsed(),
            ])
            ->havingRaw('used_count >= total_available_licenses')
            ->where('total_available_licenses', '>', 0)
            ->orderBy('name');
    }
}
