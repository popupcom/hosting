<?php

namespace App\Filament\Widgets\Dashboard;

use App\Models\DashboardPreference;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CostByCadenceStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Kosten (EK) nach Verrechnungsrhythmus';

    protected function getStats(): array
    {
        $pref = DashboardPreference::forUser();

        if ($pref->annualized_view) {
            $sums = ProjectServiceDashboardQuery::annualizedCostByCadence($pref);

            return [
                Stat::make('Monatlich (annualisiert)', Money::euro($sums[ProjectServiceDashboardQuery::CADENCE_MONTHLY]))
                    ->description('Monats-EK × 12'),
                Stat::make('Jährlich', Money::euro($sums[ProjectServiceDashboardQuery::CADENCE_YEARLY]))
                    ->description('Jahres-EK'),
                Stat::make('Einmalig', Money::euro($sums[ProjectServiceDashboardQuery::CADENCE_ONE_TIME]))
                    ->description('Ohne Hochrechnung'),
                Stat::make('Unbekanntes Intervall', Money::euro($sums[ProjectServiceDashboardQuery::CADENCE_UNKNOWN]))
                    ->description('Bitte Intervall prüfen'),
            ];
        }

        $sums = ProjectServiceDashboardQuery::sumCostByCadence($pref);

        return [
            Stat::make('Monatlich wiederkehrend', Money::euro($sums[ProjectServiceDashboardQuery::CADENCE_MONTHLY])),
            Stat::make('Jährlich wiederkehrend', Money::euro($sums[ProjectServiceDashboardQuery::CADENCE_YEARLY])),
            Stat::make('Einmalig', Money::euro($sums[ProjectServiceDashboardQuery::CADENCE_ONE_TIME])),
            Stat::make('Unbekanntes Intervall', Money::euro($sums[ProjectServiceDashboardQuery::CADENCE_UNKNOWN]))
                ->description('Intervall nicht erkannt'),
        ];
    }
}
