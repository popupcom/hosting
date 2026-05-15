<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Resources\ProjectServices\ProjectServiceResource;
use App\Filament\Support\GermanLabels;
use App\Models\DashboardPreference;
use App\Models\ProjectService;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PositionsMissingEkTableWidget extends TableWidget
{
    protected static ?int $sort = 66;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Projekt-Leistungen ohne EK')
            ->query(fn () => ProjectServiceDashboardQuery::missingCostQuery(DashboardPreference::forUser())->limit(25))
            ->paginated(false)
            ->emptyStateHeading('Keine Leistungen')
            ->emptyStateDescription('Alle gefilterten Leistungen haben einen EK.')
            ->recordUrl(fn (ProjectService $record): string => ProjectServiceResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('project.client.name')
                    ->label('Kund:in'),
                TextColumn::make('project.name')
                    ->label('Projekt'),
                TextColumn::make('effective_name')
                    ->label('Leistung'),
                TextColumn::make('serviceCatalogItem.category')
                    ->label('Kategorie')
                    ->formatStateUsing(fn ($state): string => GermanLabels::serviceCatalogCategory($state)),
                TextColumn::make('effective_sales_price')
                    ->label('VK')
                    ->money('EUR'),
            ]);
    }
}
