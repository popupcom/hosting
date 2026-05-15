<?php

namespace App\Filament\Resources\SupportPackages\Tables;

use App\Models\SupportPackage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupportPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('serviceCatalogItem'))
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('serviceCatalogItem.name')
                    ->label('Leistung (Katalog)')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('monthly_sales_price')
                    ->label('Monatlicher VK')
                    ->money('EUR')
                    ->state(fn (SupportPackage $record): ?float => $record->serviceCatalogItem?->sales_price !== null
                        ? (float) $record->serviceCatalogItem->sales_price
                        : null),
                TextColumn::make('yearly_sales_price')
                    ->label('Jährlicher VK')
                    ->money('EUR')
                    ->state(fn (SupportPackage $record): ?float => $record->serviceCatalogItem?->sales_price !== null
                        ? (float) $record->serviceCatalogItem->sales_price * 12
                        : null),
                TextColumn::make('monthly_minutes')
                    ->label('Min./Monat')
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),
                TextColumn::make('yearly_hours')
                    ->label('Std./Jahr')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Sortierung')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Aktiv'),
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
