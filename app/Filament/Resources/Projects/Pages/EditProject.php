<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addServices')
                ->label('Leistungen hinzufügen')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->url(fn (): string => ProjectResource::getUrl('add-services', ['record' => $this->getRecord()])),
            DeleteAction::make(),
        ];
    }
}
