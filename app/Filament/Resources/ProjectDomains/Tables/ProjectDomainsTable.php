<?php

namespace App\Filament\Resources\ProjectDomains\Tables;

use App\Enums\ProjectDomainStatus;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\ProjectDomain;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProjectDomainsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('domain_name')
            ->columns([
                TextColumn::make('project.name')
                    ->label('Projekt')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('domain_name')
                    ->label('Domain')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?ProjectDomainStatus $state): string => GermanLabels::projectDomainStatus($state))
                    ->color(fn (ProjectDomain $record): string => StatusBadge::projectDomain($record->status)),
                TextColumn::make('registrar')
                    ->label('Registrar')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('hosting_provider')
                    ->label('Hosting')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                TextColumn::make('billing_interval')
                    ->label('Intervall')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reminder_at')
                    ->label('ToDo fällig am')
                    ->date()
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
                SelectFilter::make('project_id')
                    ->label('Projekt')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(GermanLabels::projectDomainStatuses()),
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
