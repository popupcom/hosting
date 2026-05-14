<?php

namespace App\Filament\Resources\Licenses\Schemas;

use App\Enums\LicenseStatus;
use App\Filament\Support\GermanLabels;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LicenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lizenz')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Bezeichnung')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('vendor')
                            ->label('Anbieter')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('license_type')
                            ->label('Lizenztyp')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options(GermanLabels::licenseStatuses())
                            ->default(LicenseStatus::Active->value)
                            ->required()
                            ->native(false),
                    ]),
                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        Textarea::make('license_reference')
                            ->label('Lizenzschlüssel / Referenz')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('max_installations')
                            ->label('Max. Installationen')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('used_installations')
                            ->label('Genutzt')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        DatePicker::make('expires_at')
                            ->label('Ablaufdatum')
                            ->native(false),
                        TextInput::make('cancellation_notice_days')
                            ->label('Kündigungsfrist (Tage)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('cost_price')
                            ->label('Einkaufspreis')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('€'),
                        TextInput::make('selling_price')
                            ->label('Verkaufspreis')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('€'),
                        TextInput::make('billing_interval')
                            ->label('Abrechnungsintervall')
                            ->maxLength(32),
                        DatePicker::make('reminder_at')
                            ->label('Erinnerung am')
                            ->native(false),
                        TextInput::make('lastpass_reference')
                            ->label('LastPass-Referenz')
                            ->maxLength(512),
                    ]),
                Section::make('Projekte')
                    ->schema([
                        Select::make('projects')
                            ->label('Zugewiesene Projekte')
                            ->relationship('projects', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
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
