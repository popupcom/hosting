<?php

namespace App\Filament\Resources\ProjectServices\Pages;

use App\Filament\Resources\ProjectServices\ProjectServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProjectServices extends ListRecords
{
    protected static string $resource = ProjectServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
