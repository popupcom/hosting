<?php

namespace App\Filament\Resources\LicenseProducts\Schemas;

use App\Enums\LicenseProductStatus;
use App\Enums\LicenseSharingModel;
use App\Filament\Support\GermanLabels;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LicenseProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lizenzprodukt')
                    ->description('Zentrales Kontingent — Zuweisungen an Projekte verbrauchen verfügbare Lizenzen, ohne Verrechnung.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('provider')
                            ->label('Anbieter')
                            ->maxLength(255),
                        TextInput::make('category')
                            ->label('Kategorie')
                            ->maxLength(128),
                        TextInput::make('total_available_licenses')
                            ->label('Verfügbare Lizenzen (Kontingent)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Select::make('license_model')
                            ->label('Lizenzmodell')
                            ->options(GermanLabels::licenseSharingModels())
                            ->default(LicenseSharingModel::Shared->value)
                            ->required()
                            ->native(false),
                        Select::make('status')
                            ->label('Status')
                            ->options(GermanLabels::licenseProductStatuses())
                            ->default(LicenseProductStatus::Active->value)
                            ->required()
                            ->native(false),
                        Textarea::make('notes')
                            ->label('Notizen')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
