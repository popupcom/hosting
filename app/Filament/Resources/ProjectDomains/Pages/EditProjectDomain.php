<?php

namespace App\Filament\Resources\ProjectDomains\Pages;

use App\Filament\Resources\ProjectDomains\ProjectDomainResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProjectDomain extends EditRecord
{
    protected static string $resource = ProjectDomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
