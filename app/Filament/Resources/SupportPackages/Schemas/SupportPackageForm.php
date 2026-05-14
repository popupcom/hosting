<?php

namespace App\Filament\Resources\SupportPackages\Schemas;

use App\Enums\SupportPackageStatus;
use App\Filament\Support\GermanLabels;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupportPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Zuordnung')
                    ->columns(2)
                    ->schema([
                        Select::make('project_id')
                            ->label('Projekt')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Section::make('Paket')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Bezeichnung')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options(GermanLabels::supportPackageStatuses())
                            ->default(SupportPackageStatus::Active->value)
                            ->required()
                            ->native(false),
                        TextInput::make('response_time')
                            ->label('Reaktionszeit')
                            ->maxLength(255),
                        TextInput::make('update_interval')
                            ->label('Update-Intervall')
                            ->maxLength(255),
                        TextInput::make('price')
                            ->label('Preis')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->suffix('€'),
                        TextInput::make('billing_interval')
                            ->label('Abrechnungsintervall')
                            ->maxLength(32),
                        DatePicker::make('starts_at')
                            ->label('Startdatum')
                            ->native(false),
                        TextInput::make('cancellation_notice_days')
                            ->label('Kündigungsfrist (Tage)')
                            ->numeric()
                            ->minValue(0),
                    ]),
                Section::make('Leistungsumfang')
                    ->schema([
                        Textarea::make('scope_of_services')
                            ->label('Leistungsbeschreibung')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
                Section::make('Notizen')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notizen')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
