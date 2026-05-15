<?php

namespace App\Filament\Widgets\Dashboard;

use App\Enums\ProjectServiceStatus;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProjectServicesOverviewStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 55;

    protected ?string $heading = 'Projekt-Leistungen (Übersicht)';

    protected function getStats(): array
    {
        return [
            Stat::make('Aktiv', (string) ProjectServiceDashboardQuery::countByStatus(ProjectServiceStatus::Active))
                ->color('success'),
            Stat::make('Kündigung vorgemerkt', (string) ProjectServiceDashboardQuery::countByStatus(ProjectServiceStatus::PendingCancellation))
                ->color('warning'),
            Stat::make('Gekündigt / abgelaufen', (string) (
                ProjectServiceDashboardQuery::countByStatus(ProjectServiceStatus::Cancelled)
                + ProjectServiceDashboardQuery::countByStatus(ProjectServiceStatus::Expired)
            )),
            Stat::make('Nicht verlängern', (string) ProjectServiceDashboardQuery::countDoNotRenew())
                ->color('warning'),
            Stat::make('Endet in 60 Tagen', (string) ProjectServiceDashboardQuery::countEndingSoon(60)),
            Stat::make('Ohne Verrechnungsgruppe', (string) ProjectServiceDashboardQuery::countWithoutBillingGroup())
                ->color('gray'),
            Stat::make('Moco bereit', (string) ProjectServiceDashboardQuery::countMocoReady()),
        ];
    }
}
