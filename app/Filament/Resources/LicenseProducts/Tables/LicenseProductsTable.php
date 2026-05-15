<?php

namespace App\Filament\Resources\LicenseProducts\Tables;

use App\Enums\LicenseProductStatus;
use App\Enums\LicenseSharingModel;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\LicenseProduct;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LicenseProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount([
                'assignments as used_count' => fn (Builder $q) => $q->countsAsUsed(),
            ]))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('provider')
                    ->label('Anbieter')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('category')
                    ->label('Kategorie')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('license_model')
                    ->label('Modell')
                    ->badge()
                    ->formatStateUsing(fn (?LicenseSharingModel $state): string => GermanLabels::licenseSharingModel($state)),
                TextColumn::make('total_available_licenses')
                    ->label('Kontingent')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('used_count')
                    ->label('Belegt')
                    ->numeric(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?LicenseProductStatus $state): string => GermanLabels::licenseProductStatus($state))
                    ->color(fn (LicenseProduct $record): string => StatusBadge::licenseProduct($record->status)),
                IconColumn::make('quota_full')
                    ->label('Voll')
                    ->boolean()
                    ->state(fn (LicenseProduct $record): bool => $record->isFullyUtilized())
                    ->trueColor('danger'),
                TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(GermanLabels::licenseProductStatuses()),
                SelectFilter::make('license_model')
                    ->label('Modell')
                    ->options(GermanLabels::licenseSharingModels()),
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
