<?php

namespace App\Filament\Widgets\Dashboard;

use App\Models\DashboardPreference;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MissingPricingStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Fehlende Preise (Übersicht)';

    protected function getStats(): array
    {
        $pref = DashboardPreference::forUser();

        return [
            Stat::make('Ohne VK', (string) ProjectServiceDashboardQuery::countMissingSelling($pref)),
            Stat::make('Ohne EK', (string) ProjectServiceDashboardQuery::countMissingCost($pref)),
        ];
    }
}
