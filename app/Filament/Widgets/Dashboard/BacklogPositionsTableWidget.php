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

class BacklogPositionsTableWidget extends TableWidget
{
    protected static ?int $sort = 64;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Leistungen: Moco-Abrechnung bereit')
            ->query(fn () => ProjectServiceDashboardQuery::mocoReadyQuery(DashboardPreference::forUser())->limit(25))
            ->defaultPaginationPageOption(25)
            ->paginated(false)
            ->emptyStateHeading('Keine offenen Leistungen')
            ->emptyStateDescription('Keine aktiven Leistungen mit Status „bereit“ für die aktuellen Filter.')
            ->recordUrl(fn (ProjectService $record): string => ProjectServiceResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Angelegt')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('project.client.name')
                    ->label('Kund:in'),
                TextColumn::make('project.name')
                    ->label('Projekt'),
                TextColumn::make('effective_name')
                    ->label('Leistung'),
                TextColumn::make('effective_billing_interval')
                    ->label('Intervall')
                    ->formatStateUsing(fn ($state): string => GermanLabels::serviceCatalogBillingInterval($state))
                    ->placeholder('–'),
                TextColumn::make('effective_sales_price')
                    ->label('VK')
                    ->money('EUR'),
                TextColumn::make('effective_cost_price')
                    ->label('EK')
                    ->money('EUR'),
                TextColumn::make('moco_sync_status')
                    ->label('Moco')
                    ->formatStateUsing(fn ($state): string => GermanLabels::projectServiceMocoSyncStatus($state)),
            ]);
    }
}
