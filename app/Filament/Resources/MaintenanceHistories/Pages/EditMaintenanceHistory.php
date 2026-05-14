<?php

namespace App\Filament\Resources\MaintenanceHistories\Pages;

use App\Filament\Resources\MaintenanceHistories\MaintenanceHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMaintenanceHistory extends EditRecord
{
    protected static string $resource = MaintenanceHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
