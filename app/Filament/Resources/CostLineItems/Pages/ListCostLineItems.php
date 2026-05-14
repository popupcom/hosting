<?php

namespace App\Filament\Resources\CostLineItems\Pages;

use App\Filament\Resources\CostLineItems\CostLineItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCostLineItems extends ListRecords
{
    protected static string $resource = CostLineItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
