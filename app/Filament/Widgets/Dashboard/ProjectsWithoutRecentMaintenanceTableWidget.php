<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Services\Dashboard\SupportPackageDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ProjectsWithoutRecentMaintenanceTableWidget extends TableWidget
{
    protected static ?int $sort = 73;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Supportpaket ohne Wartung (90 Tage)';

    public function table(Table $table): Table
    {
        return $table
            ->query(SupportPackageDashboardQuery::projectsWithPackageWithoutMaintenanceQuery(90))
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('client.name')->label('Kund:in'),
                TextColumn::make('name')->label('Projekt'),
                TextColumn::make('activeProjectSupportPackage.supportPackage.name')
                    ->label('Paket'),
                TextColumn::make('last_maintenance')
                    ->label('Letzte Wartung')
                    ->state(fn (Project $record): string => SupportPackageDashboardQuery::nextMaintenanceHintForProject($record->getKey()) ?? 'Keine'),
            ])
            ->recordUrl(fn (Project $record): string => ProjectResource::getUrl('edit', ['record' => $record]));
    }
}
