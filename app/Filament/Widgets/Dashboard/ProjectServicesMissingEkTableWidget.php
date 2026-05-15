<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Resources\ProjectServices\ProjectServiceResource;
use App\Models\ProjectService;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ProjectServicesMissingEkTableWidget extends TableWidget
{
    protected static ?int $sort = 58;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Projekt-Leistungen ohne EK')
            ->query(fn () => ProjectServiceDashboardQuery::missingCostPriceQuery()->limit(25))
            ->paginated(false)
            ->emptyStateHeading('Keine Einträge')
            ->emptyStateDescription('Alle aktiven Projekt-Leistungen haben einen effektiven EK.')
            ->recordUrl(fn (ProjectService $record): string => ProjectServiceResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('project.name')
                    ->label('Projekt'),
                TextColumn::make('serviceCatalogItem.name')
                    ->label('Leistung'),
                TextColumn::make('quantity')
                    ->label('Menge'),
            ]);
    }
}
