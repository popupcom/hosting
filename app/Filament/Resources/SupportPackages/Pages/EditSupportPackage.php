<?php

namespace App\Filament\Resources\SupportPackages\Pages;

use App\Filament\Resources\SupportPackages\SupportPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSupportPackage extends EditRecord
{
    protected static string $resource = SupportPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
