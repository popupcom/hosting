<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Resources\ProjectServices\ProjectServiceResource;
use App\Filament\Support\GermanLabels;
use App\Models\ProjectService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ProjectServicesEndingSoonTableWidget extends TableWidget
{
    protected static ?int $sort = 57;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Bald endende Projekt-Leistungen (60 Tage)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProjectService::query()
                    ->endingSoon(60)
                    ->with(['project.client', 'serviceCatalogItem'])
            )
            ->columns([
                TextColumn::make('project.client.name')->label('Kund:in'),
                TextColumn::make('project.name')->label('Projekt'),
                TextColumn::make('effective_name')->label('Leistung'),
                TextColumn::make('end_date')->label('Ende')->date(),
                TextColumn::make('cancellation_date')->label('Kündigung')->date(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => GermanLabels::projectServiceStatus($state)),
            ])
            ->recordUrl(fn ($record): string => ProjectServiceResource::getUrl('edit', ['record' => $record]));
    }
}
