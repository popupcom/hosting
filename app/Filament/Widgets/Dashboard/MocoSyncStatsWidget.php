<?php

namespace App\Filament\Widgets\Dashboard;

use App\Enums\ProjectServiceMocoSyncStatus;
use App\Models\DashboardPreference;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MocoSyncStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Moco-Sync (Projekt-Leistungen)';

    protected function getStats(): array
    {
        $pref = DashboardPreference::forUser();

        return [
            Stat::make('Bereit', (string) ProjectServiceDashboardQuery::countByMocoStatus(ProjectServiceMocoSyncStatus::Ready, $pref))
                ->description('Abrechnungsbereit in Moco'),
            Stat::make('Fehler', (string) ProjectServiceDashboardQuery::countByMocoStatus(ProjectServiceMocoSyncStatus::Error, $pref))
                ->description('Manuell prüfen'),
            Stat::make('Synchronisiert', (string) ProjectServiceDashboardQuery::countByMocoStatus(ProjectServiceMocoSyncStatus::Synced, $pref)),
        ];
    }
}
