<?php

namespace App\Filament\Resources\SupportPackages\Pages;

use App\Filament\Resources\SupportPackages\SupportPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupportPackages extends ListRecords
{
    protected static string $resource = SupportPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
