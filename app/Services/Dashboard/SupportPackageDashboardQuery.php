<?php

namespace App\Services\Dashboard;

use App\Enums\ProjectSupportPackageStatus;
use App\Enums\ServiceCatalogCategory;
use App\Models\MaintenanceHistory;
use App\Models\Project;
use App\Models\ProjectSupportPackage;
use App\Models\SupportPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class SupportPackageDashboardQuery
{
    public static function countActiveAssignments(): int
    {
        return ProjectSupportPackage::query()
            ->where('status', ProjectSupportPackageStatus::Active)
            ->count();
    }

    public static function countProjectsWithoutPackage(): int
    {
        return Project::query()
            ->active()
            ->whereDoesntHave('projectSupportPackages', fn (Builder $q) => $q->active())
            ->count();
    }

    public static function yearlyRevenueFromActivePackages(): float
    {
        return (float) ProjectSupportPackage::query()
            ->active()
            ->with('supportPackage.serviceCatalogItem')
            ->get()
            ->sum(fn (ProjectSupportPackage $assignment): float => (float) ($assignment->supportPackage?->serviceCatalogItem?->sales_price ?? 0) * 12);
    }

    public static function monthlyRevenueFromActivePackages(): float
    {
        return (float) ProjectSupportPackage::query()
            ->active()
            ->with('supportPackage.serviceCatalogItem')
            ->get()
            ->sum(fn (ProjectSupportPackage $assignment): float => (float) ($assignment->supportPackage?->serviceCatalogItem?->sales_price ?? 0));
    }

    /**
     * @return Collection<int, array{name: string, count: int}>
     */
    public static function assignmentsByPackageName(): Collection
    {
        return ProjectSupportPackage::query()
            ->active()
            ->with('supportPackage')
            ->get()
            ->groupBy(fn (ProjectSupportPackage $a) => $a->supportPackage?->name ?? 'Unbekannt')
            ->map(fn (Collection $group, string $name): array => ['name' => $name, 'count' => $group->count()])
            ->values();
    }

    public static function activeAssignmentsQuery(): Builder
    {
        return ProjectSupportPackage::query()
            ->active()
            ->with(['project.client', 'supportPackage.serviceCatalogItem'])
            ->orderByDesc('start_date');
    }

    public static function projectsWithoutPackageQuery(): Builder
    {
        return Project::query()
            ->active()
            ->with('client')
            ->whereDoesntHave('projectSupportPackages', fn (Builder $q) => $q->active())
            ->orderBy('name');
    }

    public static function projectsWithPackageWithoutMaintenanceQuery(int $days = 90): Builder
    {
        $threshold = now()->subDays($days)->toDateString();

        return Project::query()
            ->active()
            ->whereHas('projectSupportPackages', fn (Builder $q) => $q->active())
            ->whereDoesntHave('maintenanceHistories', fn (Builder $q) => $q->whereDate('performed_on', '>=', $threshold))
            ->with(['client', 'activeProjectSupportPackage.supportPackage'])
            ->orderBy('name');
    }

    public static function nextMaintenanceHintForProject(int $projectId): ?string
    {
        $last = MaintenanceHistory::query()
            ->where('project_id', $projectId)
            ->orderByDesc('performed_on')
            ->value('performed_on');

        return $last ? (string) $last : null;
    }

    public static function catalogPackagesQuery(): Builder
    {
        return SupportPackage::query()
            ->activeCatalog()
            ->with('serviceCatalogItem')
            ->whereHas('serviceCatalogItem', fn (Builder $q) => $q->where('category', ServiceCatalogCategory::SupportPackage));
    }
}
