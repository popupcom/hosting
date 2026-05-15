<?php

namespace App\Services\Dashboard;

use App\Enums\ProjectServiceMocoSyncStatus;
use App\Enums\ProjectServiceStatus;
use App\Enums\ServiceCatalogBillingInterval;
use App\Enums\ServiceCatalogCategory;
use App\Models\DashboardPreference;
use App\Models\ProjectService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ProjectServiceDashboardQuery
{
    public const CADENCE_MONTHLY = 'monthly';

    public const CADENCE_YEARLY = 'yearly';

    public const CADENCE_ONE_TIME = 'one_time';

    public const CADENCE_UNKNOWN = 'unknown';

    public static function preference(): DashboardPreference
    {
        return DashboardPreference::forUser();
    }

    public static function vkExpression(): string
    {
        return 'COALESCE(project_services.custom_sales_price, project_services.sales_price_snapshot)';
    }

    public static function ekExpression(): string
    {
        return 'COALESCE(project_services.custom_cost_price, project_services.cost_price_snapshot)';
    }

    public static function quantityExpression(): string
    {
        return 'COALESCE(project_services.custom_quantity, project_services.quantity, 1)';
    }

    public static function lineVkExpression(): string
    {
        return '('.self::vkExpression().') * ('.self::quantityExpression().')';
    }

    public static function lineEkExpression(): string
    {
        return '('.self::ekExpression().') * ('.self::quantityExpression().')';
    }

    public static function intervalExpression(): string
    {
        return 'COALESCE(project_services.custom_billing_interval, project_services.billing_interval_snapshot)';
    }

    public static function baseQuery(?DashboardPreference $preference = null): Builder
    {
        $preference ??= self::preference();
        $filters = array_merge(DashboardPreference::defaultFilters(), $preference->filters ?? []);

        $query = ProjectService::query()
            ->select('project_services.*')
            ->with(['project.client', 'serviceCatalogItem']);

        if (($filters['is_active_only'] ?? true) === true) {
            $query->where('project_services.status', ProjectServiceStatus::Active);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('project_services.created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('project_services.created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['project_id'])) {
            $query->where('project_services.project_id', $filters['project_id']);
        }

        if (! empty($filters['client_id'])) {
            $query->whereHas('project', fn (Builder $q) => $q->where('client_id', $filters['client_id']));
        }

        $categories = $filters['service_categories'] ?? $filters['line_types'] ?? [];
        if ($categories !== [] && is_array($categories)) {
            $query->whereHas(
                'serviceCatalogItem',
                fn (Builder $q) => $q->whereIn('category', $categories),
            );
        }

        if (! empty($filters['moco_sync_statuses']) && is_array($filters['moco_sync_statuses'])) {
            $query->whereIn('project_services.moco_sync_status', $filters['moco_sync_statuses']);
        }

        return $query;
    }

    public static function cadence(?string $billingInterval): string
    {
        if ($billingInterval === null || $billingInterval === '') {
            return self::CADENCE_UNKNOWN;
        }

        $interval = ServiceCatalogBillingInterval::tryFrom($billingInterval);

        return match ($interval) {
            ServiceCatalogBillingInterval::Monthly => self::CADENCE_MONTHLY,
            ServiceCatalogBillingInterval::Yearly => self::CADENCE_YEARLY,
            ServiceCatalogBillingInterval::OneTime,
            ServiceCatalogBillingInterval::FlatRate => self::CADENCE_ONE_TIME,
            default => self::CADENCE_UNKNOWN,
        };
    }

    /**
     * @return array{monthly: float, yearly: float, one_time: float, unknown: float}
     */
    public static function sumSellingByCadence(?DashboardPreference $preference = null): array
    {
        return self::sumColumnByCadence(self::lineVkExpression(), $preference);
    }

    /**
     * @return array{monthly: float, yearly: float, one_time: float, unknown: float}
     */
    public static function sumCostByCadence(?DashboardPreference $preference = null): array
    {
        return self::sumColumnByCadence(self::lineEkExpression(), $preference);
    }

    /**
     * @return array{monthly: float, yearly: float, one_time: float, unknown: float}
     */
    private static function sumColumnByCadence(string $lineExpression, ?DashboardPreference $preference): array
    {
        $filters = array_merge(DashboardPreference::defaultFilters(), ($preference ?? self::preference())->filters ?? []);
        $cadenceFilter = $filters['billing_cadences'] ?? [];

        $rows = self::baseQuery($preference)
            ->selectRaw(self::intervalExpression().' as billing_interval')
            ->selectRaw("{$lineExpression} as line_total")
            ->get();

        $sums = [
            self::CADENCE_MONTHLY => 0.0,
            self::CADENCE_YEARLY => 0.0,
            self::CADENCE_ONE_TIME => 0.0,
            self::CADENCE_UNKNOWN => 0.0,
        ];

        foreach ($rows as $row) {
            $cadence = self::cadence($row->billing_interval);
            if ($cadenceFilter !== [] && is_array($cadenceFilter) && ! in_array($cadence, $cadenceFilter, true)) {
                continue;
            }
            $sums[$cadence] += (float) ($row->line_total ?? 0);
        }

        return $sums;
    }

    public static function totalSelling(?DashboardPreference $preference = null): float
    {
        return (float) self::baseQuery($preference)
            ->selectRaw('SUM('.self::lineVkExpression().') as aggregate')
            ->value('aggregate');
    }

    public static function totalCost(?DashboardPreference $preference = null): float
    {
        return (float) self::baseQuery($preference)
            ->selectRaw('SUM('.self::lineEkExpression().') as aggregate')
            ->value('aggregate');
    }

    public static function totalMargin(?DashboardPreference $preference = null): float
    {
        return self::totalSelling($preference) - self::totalCost($preference);
    }

    /**
     * @return array<string, float>
     */
    public static function annualizedSellingByCadence(?DashboardPreference $preference = null): array
    {
        $raw = self::sumSellingByCadence($preference);

        return [
            self::CADENCE_MONTHLY => $raw[self::CADENCE_MONTHLY] * 12,
            self::CADENCE_YEARLY => $raw[self::CADENCE_YEARLY],
            self::CADENCE_ONE_TIME => $raw[self::CADENCE_ONE_TIME],
            self::CADENCE_UNKNOWN => $raw[self::CADENCE_UNKNOWN],
        ];
    }

    /**
     * @return array<string, float>
     */
    public static function annualizedCostByCadence(?DashboardPreference $preference = null): array
    {
        $raw = self::sumCostByCadence($preference);

        return [
            self::CADENCE_MONTHLY => $raw[self::CADENCE_MONTHLY] * 12,
            self::CADENCE_YEARLY => $raw[self::CADENCE_YEARLY],
            self::CADENCE_ONE_TIME => $raw[self::CADENCE_ONE_TIME],
            self::CADENCE_UNKNOWN => $raw[self::CADENCE_UNKNOWN],
        ];
    }

    public static function countByMocoStatus(ProjectServiceMocoSyncStatus $status, ?DashboardPreference $preference = null): int
    {
        return (int) (clone self::baseQuery($preference))
            ->where('project_services.moco_sync_status', $status)
            ->count();
    }

    public static function countOpenForBilling(?DashboardPreference $preference = null): int
    {
        return self::countByMocoStatus(ProjectServiceMocoSyncStatus::Ready, $preference);
    }

    /**
     * @return array<int, array{category: string, revenue: float, cost: float, margin: float}>
     */
    public static function sectorBreakdown(?DashboardPreference $preference = null): array
    {
        $grouped = self::baseQuery($preference)
            ->join('service_catalog_items', 'service_catalog_items.id', '=', 'project_services.service_catalog_item_id')
            ->select([
                'service_catalog_items.category',
                DB::raw('SUM('.self::lineVkExpression().') as revenue'),
                DB::raw('SUM('.self::lineEkExpression().') as cost'),
            ])
            ->groupBy('service_catalog_items.category')
            ->get();

        $out = [];
        foreach ($grouped as $row) {
            $rev = (float) $row->revenue;
            $cost = (float) $row->cost;
            $out[] = [
                'category' => (string) $row->category,
                'revenue' => $rev,
                'cost' => $cost,
                'margin' => $rev - $cost,
            ];
        }

        return $out;
    }

    /**
     * @return Collection<int, object>
     */
    public static function topClientsBySelling(int $limit = 8, ?DashboardPreference $preference = null): Collection
    {
        return self::baseQuery($preference)
            ->join('projects', 'projects.id', '=', 'project_services.project_id')
            ->join('clients', 'clients.id', '=', 'projects.client_id')
            ->select([
                'projects.client_id',
                'clients.name as client_name',
                DB::raw('SUM('.self::lineVkExpression().') as total_vk'),
            ])
            ->groupBy('projects.client_id', 'clients.name')
            ->orderByDesc('total_vk')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public static function topClientsByCost(int $limit = 8, ?DashboardPreference $preference = null): Collection
    {
        return self::baseQuery($preference)
            ->join('projects', 'projects.id', '=', 'project_services.project_id')
            ->join('clients', 'clients.id', '=', 'projects.client_id')
            ->select([
                'projects.client_id',
                'clients.name as client_name',
                DB::raw('SUM('.self::lineEkExpression().') as total_ek'),
            ])
            ->groupBy('projects.client_id', 'clients.name')
            ->orderByDesc('total_ek')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public static function topProjectsByMargin(int $limit = 8, ?DashboardPreference $preference = null): Collection
    {
        return self::baseQuery($preference)
            ->select([
                'project_services.project_id',
                DB::raw('SUM('.self::lineVkExpression().') as total_vk'),
                DB::raw('SUM('.self::lineEkExpression().') as total_ek'),
                DB::raw('SUM('.self::lineVkExpression().') - SUM('.self::lineEkExpression().') as margin'),
            ])
            ->groupBy('project_services.project_id')
            ->orderByDesc('margin')
            ->limit($limit)
            ->with('project:id,name')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public static function lowestProjectMargins(int $limit = 8, ?DashboardPreference $preference = null): Collection
    {
        return self::baseQuery($preference)
            ->select([
                'project_services.project_id',
                DB::raw('SUM('.self::lineVkExpression().') as total_vk'),
                DB::raw('SUM('.self::lineEkExpression().') as total_ek'),
                DB::raw('SUM('.self::lineVkExpression().') - SUM('.self::lineEkExpression().') as margin'),
            ])
            ->groupBy('project_services.project_id')
            ->orderBy('margin')
            ->limit($limit)
            ->with('project:id,name')
            ->get();
    }

    public static function mocoReadyQuery(?DashboardPreference $preference = null): Builder
    {
        return self::baseQuery($preference)
            ->where('project_services.moco_sync_status', ProjectServiceMocoSyncStatus::Ready)
            ->orderBy('project_services.created_at')
            ->with(['project.client', 'serviceCatalogItem']);
    }

    public static function missingSellingQuery(?DashboardPreference $preference = null): Builder
    {
        $vk = self::vkExpression();

        return self::baseQuery($preference)
            ->where(function (Builder $q) use ($vk): void {
                $q->whereRaw("({$vk}) IS NULL")->orWhereRaw("({$vk}) = 0");
            })
            ->orderByDesc('project_services.created_at');
    }

    public static function missingCostQuery(?DashboardPreference $preference = null): Builder
    {
        $ek = self::ekExpression();

        return self::baseQuery($preference)
            ->where(function (Builder $q) use ($ek): void {
                $q->whereRaw("({$ek}) IS NULL")->orWhereRaw("({$ek}) = 0");
            })
            ->orderByDesc('project_services.created_at');
    }

    public static function countMissingSelling(?DashboardPreference $preference = null): int
    {
        return (int) self::missingSellingQuery($preference)->count();
    }

    public static function countMissingCost(?DashboardPreference $preference = null): int
    {
        return (int) self::missingCostQuery($preference)->count();
    }

    public static function countByStatus(ProjectServiceStatus $status): int
    {
        return ProjectService::query()->where('status', $status)->count();
    }

    public static function countDoNotRenew(): int
    {
        return ProjectService::query()->doNotRenew()->count();
    }

    public static function countEndingSoon(int $days = 60): int
    {
        return ProjectService::query()->endingSoon($days)->count();
    }

    public static function countWithoutBillingGroup(): int
    {
        return ProjectService::query()
            ->where('status', ProjectServiceStatus::Active)
            ->withoutBillingGroup()
            ->count();
    }

    public static function countMocoReady(): int
    {
        return ProjectService::query()
            ->where('moco_sync_status', ProjectServiceMocoSyncStatus::Ready)
            ->count();
    }

    /**
     * @return Collection<int, array{category: ServiceCatalogCategory, revenue: float, cost: float, margin: float}>
     */
    public static function revenueCostMarginByServiceCategory(?DashboardPreference $preference = null): Collection
    {
        $acc = [];
        foreach (ServiceCatalogCategory::cases() as $c) {
            $acc[$c->value] = [
                'category' => $c,
                'revenue' => 0.0,
                'cost' => 0.0,
                'margin' => 0.0,
            ];
        }

        foreach (self::baseQuery($preference)->get() as $ps) {
            $sci = $ps->serviceCatalogItem;
            if ($sci === null) {
                continue;
            }
            $key = $sci->category->value;
            $q = (float) $ps->effective_quantity;
            $vk = $ps->effective_sales_price;
            $ek = $ps->effective_cost_price;
            $acc[$key]['revenue'] += $vk !== null ? (float) $vk * $q : 0.0;
            $acc[$key]['cost'] += $ek !== null ? (float) $ek * $q : 0.0;
        }

        foreach ($acc as &$row) {
            $row['margin'] = $row['revenue'] - $row['cost'];
        }
        unset($row);

        return collect($acc)->values();
    }

    public static function monthlyRunRateActiveServices(?DashboardPreference $preference = null): float
    {
        $sum = 0.0;
        foreach (self::baseQuery($preference)->get() as $ps) {
            $total = self::lineTotal($ps);
            $interval = $ps->effective_billing_interval;
            $sum += match ($interval) {
                ServiceCatalogBillingInterval::Monthly => $total,
                ServiceCatalogBillingInterval::Yearly => $total / 12.0,
                default => 0.0,
            };
        }

        return $sum;
    }

    public static function yearlyRunRateActiveServices(?DashboardPreference $preference = null): float
    {
        $sum = 0.0;
        foreach (self::baseQuery($preference)->get() as $ps) {
            $total = self::lineTotal($ps);
            $interval = $ps->effective_billing_interval;
            $sum += match ($interval) {
                ServiceCatalogBillingInterval::Monthly => $total * 12.0,
                ServiceCatalogBillingInterval::Yearly => $total,
                default => 0.0,
            };
        }

        return $sum;
    }

    private static function lineTotal(ProjectService $ps): float
    {
        $vk = $ps->effective_sales_price;
        if ($vk === null) {
            return 0.0;
        }

        return (float) $vk * (float) $ps->effective_quantity;
    }

    /**
     * @return Collection<int, array{name: string, revenue: float, catalog_item_id: int}>
     */
    public static function topServicesByRevenue(int $limit = 10, ?DashboardPreference $preference = null): Collection
    {
        return self::baseQuery($preference)
            ->get()
            ->groupBy('service_catalog_item_id')
            ->map(function (Collection $group): array {
                /** @var ProjectService $first */
                $first = $group->first();
                $name = $first->serviceCatalogItem?->name ?? ('#'.$first->service_catalog_item_id);
                $revenue = $group->sum(fn (ProjectService $ps): float => self::lineTotal($ps));

                return [
                    'catalog_item_id' => (int) $first->service_catalog_item_id,
                    'name' => $name,
                    'revenue' => $revenue,
                ];
            })
            ->sortByDesc('revenue')
            ->values()
            ->take($limit);
    }
}
