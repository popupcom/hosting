<?php

namespace App\Filament\Resources\LicenseProducts\Schemas;

use App\Enums\LicenseProductStatus;
use App\Enums\LicenseSharingModel;
use App\Filament\Support\GermanLabels;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LicenseProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lizenzprodukt')
                    ->description('Katalog und Kontingent — welche Lizenzen vorhanden sind und wie sie Projekten zugewiesen werden.')
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
                            ->options([
                                LicenseSharingModel::Shared->value => GermanLabels::licenseSharingModel(LicenseSharingModel::Shared),
                                LicenseSharingModel::Dedicated->value => GermanLabels::licenseSharingModel(LicenseSharingModel::Dedicated),
                            ])
                            ->default(LicenseSharingModel::Shared->value)
                            ->required()
                            ->live()
                            ->native(false)
                            ->helperText('Geteilt: ein Code für viele Projekte. Dediziert: eigener Code je Projekt.'),
                        Select::make('status')
                            ->label('Status')
                            ->options(GermanLabels::licenseProductStatuses())
                            ->default(LicenseProductStatus::Active->value)
                            ->required()
                            ->native(false),
                        TextInput::make('shared_license_code')
                            ->label('Gemeinsamer Lizenzcode')
                            ->password()
                            ->revealable()
                            ->maxLength(2048)
                            ->required(fn (Get $get): bool => $get('license_model') === LicenseSharingModel::Shared->value
                                && ! (bool) $get('requires_individual_license_code'))
                            ->visible(fn (Get $get): bool => $get('license_model') === LicenseSharingModel::Shared->value)
                            ->columnSpanFull(),
                        Toggle::make('requires_individual_license_code')
                            ->label('Individuellen Code je Projekt verlangen')
                            ->helperText('Auch bei geteiltem Modell pro Zuweisung einen eigenen Code erfassen.')
                            ->visible(fn (Get $get): bool => $get('license_model') === LicenseSharingModel::Shared->value)
                            ->live(),
                        Textarea::make('notes')
                            ->label('Notizen')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
