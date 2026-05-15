<?php

namespace App\Filament\Widgets\Dashboard;

use App\Enums\ServiceCatalogCategory;
use App\Filament\Support\GermanLabels;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class ServiceCatalogCategoryFinancialTableWidget extends TableWidget
{
    protected static ?int $sort = 55;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Projekt-Leistungen: Einnahmen, Kosten, Marge nach Leistungskategorie (aktiv)')
            ->paginated(false)
            ->emptyStateHeading('Keine Daten')
            ->emptyStateDescription('Es sind keine aktiven Projekt-Leistungen erfasst.')
            ->records(fn (): Collection => ProjectServiceDashboardQuery::revenueCostMarginByServiceCategory())
            ->columns([
                TextColumn::make('category')
                    ->label('Kategorie')
                    ->formatStateUsing(fn (?ServiceCatalogCategory $state): string => GermanLabels::serviceCatalogCategory(
                        $state instanceof ServiceCatalogCategory
                            ? $state
                            : ServiceCatalogCategory::tryFrom((string) $state),
                    )),
                TextColumn::make('revenue')
                    ->label('Einnahmen (VK)')
                    ->money('EUR'),
                TextColumn::make('cost')
                    ->label('Kosten (EK)')
                    ->money('EUR'),
                TextColumn::make('margin')
                    ->label('Marge')
                    ->money('EUR'),
            ]);
    }
}
