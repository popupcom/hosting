<?php

namespace App\Filament\Resources\SupportPackages\Tables;

use App\Enums\SupportPackageStatus;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\SupportPackage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('project.name')
                    ->label('Projekt')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Bezeichnung')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?SupportPackageStatus $state): string => GermanLabels::supportPackageStatus($state))
                    ->color(fn (SupportPackage $record): string => StatusBadge::supportPackage($record->status)),
                TextColumn::make('price')
                    ->label('Preis')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('billing_interval')
                    ->label('Intervall')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('starts_at')
                    ->label('Start')
                    ->date()
                    ->sortable(),
                TextColumn::make('response_time')
                    ->label('Reaktionszeit')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('update_interval')
                    ->label('Updates')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Aktualisiert')
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(GermanLabels::supportPackageStatuses()),
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
