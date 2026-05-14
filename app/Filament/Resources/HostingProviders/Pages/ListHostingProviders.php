<?php

namespace App\Filament\Resources\HostingProviders\Pages;

use App\Filament\Resources\HostingProviders\HostingProviderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHostingProviders extends ListRecords
{
    protected static string $resource = HostingProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
