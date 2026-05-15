<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Services\Dashboard\SupportPackageDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ProjectsWithoutSupportPackageTableWidget extends TableWidget
{
    protected static ?int $sort = 72;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Projekte ohne Supportpaket';

    public function table(Table $table): Table
    {
        return $table
            ->query(SupportPackageDashboardQuery::projectsWithoutPackageQuery())
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('client.name')->label('Kund:in'),
                TextColumn::make('name')->label('Projekt'),
                TextColumn::make('url')->label('URL')->limit(32),
            ])
            ->recordUrl(fn (Project $record): string => ProjectResource::getUrl('edit', ['record' => $record]));
    }
}
