<?php

namespace App\Filament\Widgets\Dashboard;

use App\Services\Dashboard\SupportPackageDashboardQuery;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupportPackageOverviewStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 70;

    protected ?string $heading = 'Supportpakete';

    protected function getStats(): array
    {
        $byPackage = SupportPackageDashboardQuery::assignmentsByPackageName();

        $stats = [
            Stat::make('Aktive Zuweisungen', (string) SupportPackageDashboardQuery::countActiveAssignments())
                ->color('success'),
            Stat::make('Ohne Supportpaket', (string) SupportPackageDashboardQuery::countProjectsWithoutPackage())
                ->color('warning'),
            Stat::make('Jährlicher VK (rechnerisch)', number_format(SupportPackageDashboardQuery::yearlyRevenueFromActivePackages(), 2, ',', '.').' €')
                ->description('Summe aktiver Pakete × 12')
                ->color('primary'),
            Stat::make('Monatlicher VK (rechnerisch)', number_format(SupportPackageDashboardQuery::monthlyRevenueFromActivePackages(), 2, ',', '.').' €')
                ->description('Summe aktiver Pakete')
                ->color('info'),
        ];

        foreach ($byPackage as $row) {
            $stats[] = Stat::make($row['name'], (string) $row['count'])
                ->description('aktive Projekte');
        }

        return $stats;
    }
}
