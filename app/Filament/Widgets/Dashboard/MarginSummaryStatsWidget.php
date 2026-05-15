<?php

namespace App\Filament\Widgets\Dashboard;

use App\Models\DashboardPreference;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MarginSummaryStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Deckungsbeitrag & offene Verrechnung';

    protected function getStats(): array
    {
        $pref = DashboardPreference::forUser();
        $vk = ProjectServiceDashboardQuery::totalSelling($pref);
        $ek = ProjectServiceDashboardQuery::totalCost($pref);
        $margin = ProjectServiceDashboardQuery::totalMargin($pref);
        $open = ProjectServiceDashboardQuery::countOpenForBilling($pref);

        $marginPct = $vk > 0 ? (($margin / $vk) * 100) : 0.0;

        return [
            Stat::make('Summe VK', Money::euro($vk)),
            Stat::make('Summe EK', Money::euro($ek)),
            Stat::make('Marge (VK − EK)', Money::euro($margin))
                ->description(sprintf('%.1f %% vom VK', $marginPct)),
            Stat::make('Moco bereit', (string) $open)
                ->description('Aktive Leistungen mit Status „bereit“'),
        ];
    }
}
