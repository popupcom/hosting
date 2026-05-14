<?php

namespace App\Filament\Resources\CostLineItems\Tables;

use App\Enums\CostLineItemType;
use App\Enums\MocoSyncStatus;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\CostLineItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CostLineItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('client.name')
                    ->label('Kund:in')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.name')
                    ->label('Projekt')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('line_type')
                    ->label('Art')
                    ->badge()
                    ->formatStateUsing(fn (?CostLineItemType $state): string => GermanLabels::costLineItemType($state))
                    ->color(fn (CostLineItem $record): string => StatusBadge::costLineType($record->line_type)),
                TextColumn::make('billable_type')
                    ->label('Bezug-Typ')
                    ->formatStateUsing(fn (?string $state): string => GermanLabels::billableMorphType($state))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('billable_title')
                    ->label('Objekt')
                    ->getStateUsing(fn (CostLineItem $record): string => GermanLabels::billableTitle($record->billable)),
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
                TextColumn::make('billing_interval')
                    ->label('Intervall')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('moco_sync_status')
                    ->label('Moco')
                    ->badge()
                    ->formatStateUsing(fn (?MocoSyncStatus $state): string => GermanLabels::mocoSyncStatus($state))
                    ->color(fn (CostLineItem $record): string => StatusBadge::mocoSync($record->moco_sync_status)),
                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean(),
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
                SelectFilter::make('client_id')
                    ->label('Kund:in')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('project_id')
                    ->label('Projekt')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('line_type')
                    ->label('Art')
                    ->options(GermanLabels::costLineItemTypes()),
                SelectFilter::make('moco_sync_status')
                    ->label('Moco-Sync')
                    ->options(GermanLabels::mocoSyncStatuses()),
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
