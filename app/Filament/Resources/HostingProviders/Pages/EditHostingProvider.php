<?php

namespace App\Filament\Resources\HostingProviders\Pages;

use App\Filament\Resources\HostingProviders\HostingProviderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHostingProvider extends EditRecord
{
    protected static string $resource = HostingProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
