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
            Stat::make('Aktive Zuweisungen', (string) LicenseUsageDashboardQuery::countActiveAssignments())
                ->color('success'),
            Stat::make('Kündigung vorgemerkt', (string) LicenseUsageDashboardQuery::countPendingCancellation())
                ->color('warning'),
            Stat::make('Kontingent voll', (string) LicenseUsageDashboardQuery::countFullyUtilizedProducts())
                ->color('danger'),
        ];
    }
}
