<?php

namespace App\Filament\Resources\Reminders\Pages;

use App\Filament\Resources\Reminders\ReminderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReminder extends CreateRecord
{
    protected static string $resource = ReminderResource::class;

    public function getTitle(): string
    {
        return 'ToDo erstellen';
    }

    protected function getCreateFormActionLabel(): string
    {
        return 'ToDo speichern';
    }
}
