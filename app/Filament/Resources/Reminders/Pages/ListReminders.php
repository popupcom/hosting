<?php

namespace App\Filament\Resources\Reminders\Pages;

use App\Filament\Resources\Reminders\ReminderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReminders extends ListRecords
{
    protected static string $resource = ReminderResource::class;

    public function getTitle(): string
    {
        return 'ToDo Übersicht';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('ToDo erstellen'),
        ];
    }
}
