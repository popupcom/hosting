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

class PositionsMissingVkTableWidget extends TableWidget
{
    protected static ?int $sort = 65;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Projekt-Leistungen ohne VK')
            ->query(fn () => ProjectServiceDashboardQuery::missingSellingQuery(DashboardPreference::forUser())->limit(25))
            ->paginated(false)
            ->emptyStateHeading('Keine Leistungen')
            ->emptyStateDescription('Alle gefilterten Leistungen haben einen VK.')
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
                TextColumn::make('effective_cost_price')
                    ->label('EK')
                    ->money('EUR'),
            ]);
    }
}
