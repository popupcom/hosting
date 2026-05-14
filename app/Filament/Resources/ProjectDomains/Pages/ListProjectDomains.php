<?php

namespace App\Filament\Resources\ProjectDomains\Pages;

use App\Filament\Resources\ProjectDomains\ProjectDomainResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProjectDomains extends ListRecords
{
    protected static string $resource = ProjectDomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
