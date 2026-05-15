<?php

namespace App\Filament\Resources\Reminders\Tables;

use App\Enums\ReminderStatus;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\Reminder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RemindersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('reminder_at')
            ->columns([
                TextColumn::make('remindable_type')
                    ->label('Gruppe')
                    ->formatStateUsing(fn (?string $state): string => GermanLabels::todoRemindableType($state))
                    ->badge()
                    ->sortable(),
                TextColumn::make('remindable')
                    ->label('Bezug')
                    ->formatStateUsing(fn (Reminder $record): string => GermanLabels::todoRemindableTitle($record->remindable)),
                TextColumn::make('assignedUser.name')
                    ->label('Zugewiesen an')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reminder_at')
                    ->label('Fällig am')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('ToDo Status')
                    ->badge()
                    ->formatStateUsing(fn (?ReminderStatus $state): string => GermanLabels::todoStatus($state))
                    ->color(fn (Reminder $record): string => StatusBadge::todo($record->status)),
                IconColumn::make('is_done')
                    ->label('Erledigt')
                    ->boolean(),
                TextColumn::make('message')
                    ->label('Nachricht')
                    ->limit(50)
                    ->tooltip(fn (Reminder $record): string => (string) $record->message)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('assigned_user_id')
                    ->label('Zugewiesen an')
                    ->relationship('assignedUser', 'name', fn ($query) => $query->orderBy('name'))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('remindable_type')
                    ->label('Gruppe')
                    ->options(GermanLabels::todoRemindableTypes()),
                SelectFilter::make('status')
                    ->label('ToDo Status')
                    ->options(GermanLabels::todoStatuses()),
                TernaryFilter::make('is_done')
                    ->label('ToDo erledigt'),
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
