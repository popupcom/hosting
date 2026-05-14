<?php

namespace App\Filament\Resources\Licenses\Tables;

use App\Enums\LicenseStatus;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\License;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LicensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Bezeichnung')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vendor')
                    ->label('Anbieter')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('license_type')
                    ->label('Typ')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?LicenseStatus $state): string => GermanLabels::licenseStatus($state))
                    ->color(fn (License $record): string => StatusBadge::license($record->status)),
                TextColumn::make('used_installations')
                    ->label('Genutzt')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_installations')
                    ->label('Max.')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expires_at')
                    ->label('Ablauf')
                    ->date()
                    ->sortable(),
                TextColumn::make('cost_price')
                    ->label('EK')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('selling_price')
                    ->label('VK')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('projects_count')
                    ->label('Projekte')
                    ->counts('projects')
                    ->sortable(),
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(GermanLabels::licenseStatuses()),
                SelectFilter::make('vendor')
                    ->label('Anbieter')
                    ->options(fn (): array => License::query()->distinct()->orderBy('vendor')->pluck('vendor', 'vendor')->all()),
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
