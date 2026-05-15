<?php

namespace App\Filament\Widgets\Dashboard;

use App\Services\Dashboard\ProjectServiceDashboardQuery;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProjectServiceRunRateStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 56;

    protected ?string $heading = 'Laufende Einnahmen (aktive Projekt-Leistungen)';

    protected function getStats(): array
    {
        $m = ProjectServiceDashboardQuery::monthlyRunRateActiveServices();
        $y = ProjectServiceDashboardQuery::yearlyRunRateActiveServices();

        return [
            Stat::make('Monatlicher Run-Rate (VK)', Money::euro($m))
                ->description('Monatlich abgerechnet voll; jährlich / 12'),
            Stat::make('Jährlicher Run-Rate (VK)', Money::euro($y))
                ->description('Jährlich voll; monatlich × 12'),
        ];
    }
}
