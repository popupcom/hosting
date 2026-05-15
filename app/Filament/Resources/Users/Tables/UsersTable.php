<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-Mail')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Rolle')
                    ->formatStateUsing(fn (?string $state): string => UserRole::labelFor($state))
                    ->badge(),
                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean(),
                TextColumn::make('notificationGroups.name')
                    ->label('Gruppen')
                    ->badge()
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
