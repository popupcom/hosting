<?php

namespace App\Filament\Resources\MaintenanceHistories\Tables;

use App\Enums\MaintenanceType;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\MaintenanceHistory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MaintenanceHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('performed_on', 'desc')
            ->columns([
                TextColumn::make('project.name')
                    ->label('Projekt')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('maintenance_type')
                    ->label('Art')
                    ->badge()
                    ->formatStateUsing(fn (?MaintenanceType $state): string => GermanLabels::maintenanceType($state))
                    ->color(fn (MaintenanceHistory $record): string => StatusBadge::maintenanceType($record->maintenance_type)),
                TextColumn::make('performed_by')
                    ->label('Von')
                    ->searchable(),
                TextColumn::make('performed_on')
                    ->label('Datum')
                    ->date()
                    ->sortable(),
                IconColumn::make('has_errors')
                    ->label('Fehler')
                    ->boolean(),
                TextColumn::make('result')
                    ->label('Ergebnis')
                    ->limit(60)
                    ->tooltip(fn (MaintenanceHistory $record): string => (string) $record->result)
                    ->wrap(),
                TextColumn::make('managewp_reference')
                    ->label('ManageWP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->label('Projekt')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('maintenance_type')
                    ->label('Art')
                    ->options(GermanLabels::maintenanceTypes()),
                TernaryFilter::make('has_errors')
                    ->label('Mit Fehlern'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
