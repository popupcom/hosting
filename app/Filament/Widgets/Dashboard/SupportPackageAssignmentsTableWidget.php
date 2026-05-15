<?php

namespace App\Filament\Widgets\Dashboard;

use App\Enums\ProjectSupportPackageStatus;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\ProjectSupportPackage;
use App\Services\Dashboard\SupportPackageDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class SupportPackageAssignmentsTableWidget extends TableWidget
{
    protected static ?int $sort = 71;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Aktive Supportpakete je Projekt';

    public function table(Table $table): Table
    {
        return $table
            ->query(SupportPackageDashboardQuery::activeAssignmentsQuery())
            ->paginated([10, 25])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('project.client.name')->label('Kund:in'),
                TextColumn::make('project.name')->label('Projekt'),
                TextColumn::make('supportPackage.name')->label('Paket'),
                TextColumn::make('supportPackage.serviceCatalogItem.name')->label('Katalog-Leistung'),
                TextColumn::make('yearly_vk')
                    ->label('Jährlicher VK')
                    ->state(fn (ProjectSupportPackage $record): string => ($m = $record->supportPackage?->serviceCatalogItem?->sales_price) !== null
                        ? number_format((float) $m * 12, 2, ',', '.').' €'
                        : '—'),
                TextColumn::make('start_date')->label('Start')->date(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?ProjectSupportPackageStatus $state): string => GermanLabels::projectSupportPackageStatus($state))
                    ->color(fn (ProjectSupportPackage $record): string => StatusBadge::projectSupportPackage($record->status)),
            ])
            ->recordUrl(fn (ProjectSupportPackage $record): string => ProjectResource::getUrl('edit', ['record' => $record->project_id]));
    }
}
