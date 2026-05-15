<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Resources\ProjectServices\ProjectServiceResource;
use App\Models\ProjectService;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ProjectServicesMissingVkTableWidget extends TableWidget
{
    protected static ?int $sort = 57;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Projekt-Leistungen ohne VK')
            ->query(fn () => ProjectServiceDashboardQuery::missingSalesPriceQuery()->limit(25))
            ->paginated(false)
            ->emptyStateHeading('Keine Einträge')
            ->emptyStateDescription('Alle aktiven Projekt-Leistungen haben einen effektiven VK.')
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
