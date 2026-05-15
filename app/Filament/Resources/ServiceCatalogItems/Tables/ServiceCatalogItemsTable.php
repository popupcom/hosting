<?php

namespace App\Filament\Resources\ServiceCatalogItems\Tables;

use App\Enums\ServiceCatalogBillingInterval;
use App\Enums\ServiceCatalogCategory;
use App\Enums\ServiceCatalogUnit;
use App\Filament\Support\GermanLabels;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ServiceCatalogItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('minimum_term_months')
                    ->label('Mindestlaufzeit (Mo.)')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category')
                    ->label('Kategorie')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => GermanLabels::serviceCatalogCategory(
                        $state instanceof ServiceCatalogCategory
                            ? $state
                            : ServiceCatalogCategory::tryFrom((string) $state),
                    )),
                TextColumn::make('unit')
                    ->label('Einheit')
                    ->formatStateUsing(fn ($state): string => GermanLabels::serviceCatalogUnit(
                        $state instanceof ServiceCatalogUnit
                            ? $state
                            : ServiceCatalogUnit::tryFrom((string) $state),
                    )),
                TextColumn::make('default_quantity')
                    ->label('Std.-Menge')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.'),
                TextColumn::make('billing_interval')
                    ->label('Intervall')
                    ->formatStateUsing(fn ($state): string => GermanLabels::serviceCatalogBillingInterval(
                        $state instanceof ServiceCatalogBillingInterval
                            ? $state
                            : ServiceCatalogBillingInterval::tryFrom((string) $state),
                    )),
                TextColumn::make('cost_price')
                    ->label('EK')
                    ->money('EUR'),
                TextColumn::make('sales_price')
                    ->label('VK')
                    ->money('EUR'),
                TextColumn::make('margin_amount')
                    ->label('Marge')
                    ->formatStateUsing(function ($record): string {
                        $m = $record->margin_amount;

                        return $m === null ? '–' : number_format($m, 2, ',', '.').' €';
                    }),
                TextColumn::make('margin_percentage')
                    ->label('Marge %')
                    ->formatStateUsing(function ($record): string {
                        $p = $record->margin_percentage;

                        return $p === null ? '–' : number_format($p, 1, ',', '.').' %';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean(),
                TextColumn::make('moco_article_id')
                    ->label('Moco')
                    ->placeholder('–')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategorie')
                    ->options(GermanLabels::serviceCatalogCategories()),
                TernaryFilter::make('is_active')
                    ->label('Aktiv'),
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
