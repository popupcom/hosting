<?php

namespace App\Filament\Resources\LicenseProducts\Pages;

use App\Filament\Resources\LicenseProducts\LicenseProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLicenseProducts extends ListRecords
{
    protected static string $resource = LicenseProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
