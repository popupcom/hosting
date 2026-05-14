<?php

namespace App\Filament\Resources\Servers\Tables;

use App\Enums\ServerStatus;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\Server;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('hostingProvider.name')
                    ->label('Anbieter')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('hostname')
                    ->label('Hostname')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable(),
                TextColumn::make('region')
                    ->label('Region')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?ServerStatus $state): string => GermanLabels::serverStatus($state))
                    ->color(fn (Server $record): string => StatusBadge::server($record->status)),
                TextColumn::make('operating_system')
                    ->label('OS')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('php_versions')
                    ->label('PHP')
                    ->formatStateUsing(fn (?array $state): string => $state ? implode(', ', $state) : '–')
                    ->toggleable(),
                TextColumn::make('contract_expires_at')
                    ->label('Vertragsende')
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
                SelectFilter::make('hosting_provider_id')
                    ->label('Anbieter')
                    ->relationship('hostingProvider', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(GermanLabels::serverStatuses()),
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
