<?php

namespace App\Filament\Resources\LicenseProducts\Pages;

use App\Filament\Resources\LicenseProducts\LicenseProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLicenseProduct extends EditRecord
{
    protected static string $resource = LicenseProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
