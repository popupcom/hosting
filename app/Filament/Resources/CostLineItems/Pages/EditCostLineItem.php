<?php

namespace App\Filament\Resources\CostLineItems\Pages;

use App\Filament\Resources\CostLineItems\CostLineItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCostLineItem extends EditRecord
{
    protected static string $resource = CostLineItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
