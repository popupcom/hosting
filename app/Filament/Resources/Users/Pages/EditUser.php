<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Support\UserDeletionGuard;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (): void {
                    $record = $this->getRecord();

                    if (! $record instanceof User) {
                        return;
                    }

                    $reason = UserDeletionGuard::deletionBlockedReason($record);

                    if ($reason === null) {
                        return;
                    }

                    Notification::make()
                        ->title($reason)
                        ->danger()
                        ->send();

                    $this->halt();
                }),
        ];
    }
}
