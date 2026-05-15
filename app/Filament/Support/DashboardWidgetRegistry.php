<?php

namespace App\Filament\Support;

use App\Filament\Widgets\Dashboard\BacklogPositionsTableWidget;
use App\Filament\Widgets\Dashboard\CostByCadenceStatsWidget;
use App\Filament\Widgets\Dashboard\LicenseAssignmentsEndingSoonTableWidget;
use App\Filament\Widgets\Dashboard\LicenseUsageOverviewStatsWidget;
use App\Filament\Widgets\Dashboard\MarginSummaryStatsWidget;
use App\Filament\Widgets\Dashboard\MissingPricingStatsWidget;
use App\Filament\Widgets\Dashboard\MocoSyncStatsWidget;
use App\Filament\Widgets\Dashboard\PositionsMissingEkTableWidget;
use App\Filament\Widgets\Dashboard\PositionsMissingVkTableWidget;
use App\Filament\Widgets\Dashboard\ProjectMarginHighTableWidget;
use App\Filament\Widgets\Dashboard\ProjectMarginLowTableWidget;
use App\Filament\Widgets\Dashboard\ProjectServiceRunRateStatsWidget;
use App\Filament\Widgets\Dashboard\ProjectServicesEndingSoonTableWidget;
use App\Filament\Widgets\Dashboard\ProjectServicesMissingEkTableWidget;
use App\Filament\Widgets\Dashboard\ProjectServicesMissingVkTableWidget;
use App\Filament\Widgets\Dashboard\ProjectServicesOverviewStatsWidget;
use App\Filament\Widgets\Dashboard\RecentChangeLogsTableWidget;
use App\Filament\Widgets\Dashboard\RevenueByCadenceStatsWidget;
use App\Filament\Widgets\Dashboard\SectorFinancialTableWidget;
use App\Filament\Widgets\Dashboard\ServiceCatalogCategoryFinancialTableWidget;
use App\Filament\Widgets\Dashboard\TopClientsCostTableWidget;
use App\Filament\Widgets\Dashboard\TopClientsRevenueTableWidget;
use App\Filament\Widgets\Dashboard\TopProjectServicesByRevenueTableWidget;
use App\Filament\Widgets\Dashboard\UnreadNotificationsStatsWidget;
use Filament\Widgets\Widget;

final class DashboardWidgetRegistry
{
    /**
     * @return array<int, class-string<Widget>>
     */
    public static function widgetClasses(): array
    {
        return [
            RevenueByCadenceStatsWidget::class,
            CostByCadenceStatsWidget::class,
            MarginSummaryStatsWidget::class,
            MocoSyncStatsWidget::class,
            SectorFinancialTableWidget::class,
            TopClientsRevenueTableWidget::class,
            TopClientsCostTableWidget::class,
            ProjectMarginHighTableWidget::class,
            ProjectMarginLowTableWidget::class,
            ServiceCatalogCategoryFinancialTableWidget::class,
            ProjectServicesOverviewStatsWidget::class,
            LicenseUsageOverviewStatsWidget::class,
            LicenseAssignmentsEndingSoonTableWidget::class,
            UnreadNotificationsStatsWidget::class,
            RecentChangeLogsTableWidget::class,
            ProjectServiceRunRateStatsWidget::class,
            ProjectServicesEndingSoonTableWidget::class,
            ProjectServicesMissingVkTableWidget::class,
            ProjectServicesMissingEkTableWidget::class,
            TopProjectServicesByRevenueTableWidget::class,
            BacklogPositionsTableWidget::class,
            MissingPricingStatsWidget::class,
            PositionsMissingVkTableWidget::class,
            PositionsMissingEkTableWidget::class,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            RevenueByCadenceStatsWidget::class => 'Einnahmen nach Verrechnungsrhythmus',
            CostByCadenceStatsWidget::class => 'Kosten nach Verrechnungsrhythmus',
            MarginSummaryStatsWidget::class => 'Deckungsbeitrag & Marge',
            MocoSyncStatsWidget::class => 'Moco-Sync Übersicht',
            SectorFinancialTableWidget::class => 'Einnahmen / Kosten nach Bereich',
            TopClientsRevenueTableWidget::class => 'Top Einnahmen je Kund:in',
            TopClientsCostTableWidget::class => 'Top Kosten je Kund:in',
            ServiceCatalogCategoryFinancialTableWidget::class => 'Projekt-Leistungen: Finanzen nach Kategorie',
            ProjectServicesOverviewStatsWidget::class => 'Projekt-Leistungen: Status & Verlängerung',
            LicenseUsageOverviewStatsWidget::class => 'Lizenznutzung: Kontingent & Zuweisungen',
            LicenseAssignmentsEndingSoonTableWidget::class => 'Lizenz-Zuweisungen: enden bald',
            UnreadNotificationsStatsWidget::class => 'Ungelesene In-App-Benachrichtigungen',
            RecentChangeLogsTableWidget::class => 'Letzte Änderungen (Protokoll)',
            ProjectServiceRunRateStatsWidget::class => 'Projekt-Leistungen: monatlicher/jährlicher Run-Rate',
            ProjectServicesEndingSoonTableWidget::class => 'Projekt-Leistungen: enden bald',
            ProjectServicesMissingVkTableWidget::class => 'Projekt-Leistungen ohne VK',
            ProjectServicesMissingEkTableWidget::class => 'Projekt-Leistungen ohne EK',
            TopProjectServicesByRevenueTableWidget::class => 'Top-Leistungen nach Umsatz',
            ProjectMarginHighTableWidget::class => 'Höchste Marge je Projekt',
            ProjectMarginLowTableWidget::class => 'Niedrige oder negative Marge',
            BacklogPositionsTableWidget::class => 'Leistungen: Moco bereit',
            MissingPricingStatsWidget::class => 'Leistungen ohne EK / VK (Zahlen)',
            PositionsMissingVkTableWidget::class => 'Leistungen ohne VK',
            PositionsMissingEkTableWidget::class => 'Leistungen ohne EK',
        ];
    }
}
