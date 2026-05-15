<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Support\UserDeletionGuard;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

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
                DeleteAction::make()
                    ->before(function (DeleteAction $action, User $record): void {
                        $reason = UserDeletionGuard::deletionBlockedReason($record);

                        if ($reason === null) {
                            return;
                        }

                        Notification::make()
                            ->title($reason)
                            ->danger()
                            ->send();

                        $action->halt();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, Collection $records): void {
                            foreach ($records as $record) {
                                if (! $record instanceof User) {
                                    continue;
                                }

                                $reason = UserDeletionGuard::deletionBlockedReason($record);

                                if ($reason === null) {
                                    continue;
                                }

                                Notification::make()
                                    ->title($reason)
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}
