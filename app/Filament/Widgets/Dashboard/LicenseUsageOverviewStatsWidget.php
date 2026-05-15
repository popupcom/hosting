<?php

namespace App\Filament\Widgets\Dashboard;

use App\Services\Dashboard\LicenseUsageDashboardQuery;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LicenseUsageOverviewStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 56;

    protected ?string $heading = 'Lizenznutzung';

    protected function getStats(): array
    {
        return [
            Stat::make('Aktive Nutzungen', (string) LicenseUsageDashboardQuery::countActiveAssignments())
                ->description('Projekt-Zuweisungen aktiv')
                ->color('success'),
            Stat::make('Shared-Produkte', (string) LicenseUsageDashboardQuery::countSharedProducts())
                ->description('Ein Code für viele Projekte')
                ->color('info'),
            Stat::make('Dedicated-Produkte', (string) LicenseUsageDashboardQuery::countDedicatedProducts())
                ->description('Eigener Code je Projekt')
                ->color('primary'),
            Stat::make('Hohe Auslastung', (string) LicenseUsageDashboardQuery::countHighUtilizationProducts())
                ->description('≥ 80 % Kontingent belegt')
                ->color('warning'),
            Stat::make('Kontingent voll', (string) LicenseUsageDashboardQuery::countFullyUtilizedProducts())
                ->color('danger'),
        ];
    }
}
