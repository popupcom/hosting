<?php

namespace App\Filament\Widgets\Dashboard;

use App\Enums\ReminderStatus;
use App\Filament\Resources\Reminders\ReminderResource;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\Reminder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class CriticalTodosTableWidget extends TableWidget
{
    protected static ?int $sort = 24;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Kritische ToDos';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reminder::query()
                    ->critical()
                    ->with(['remindable', 'assignedUser'])
                    ->orderBy('reminder_at'),
            )
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Keine kritischen ToDos')
            ->columns([
                TextColumn::make('remindable_type')
                    ->label('Gruppe')
                    ->formatStateUsing(fn (?string $state): string => GermanLabels::todoRemindableType($state))
                    ->badge(),
                TextColumn::make('remindable')
                    ->label('Bezug')
                    ->formatStateUsing(fn (Reminder $record): string => GermanLabels::todoRemindableTitle($record->remindable)),
                TextColumn::make('assignedUser.name')
                    ->label('Zugewiesen an')
                    ->placeholder('—'),
                TextColumn::make('reminder_at')
                    ->label('Fällig am')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('ToDo Status')
                    ->badge()
                    ->formatStateUsing(fn (?ReminderStatus $state): string => GermanLabels::todoStatus($state))
                    ->color(fn (Reminder $record): string => StatusBadge::todo($record->status)),
                TextColumn::make('message')
                    ->label('Nachricht')
                    ->limit(40),
            ])
            ->recordUrl(fn (Reminder $record): string => ReminderResource::getUrl('edit', ['record' => $record]));
    }
}
