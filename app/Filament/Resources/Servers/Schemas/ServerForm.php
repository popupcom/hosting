<?php

namespace App\Filament\Resources\Servers\Schemas;

use App\Enums\ServerStatus;
use App\Filament\Support\GermanLabels;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Zuordnung')
                    ->columns(2)
                    ->schema([
                        Select::make('hosting_provider_id')
                            ->label('Hosting-Anbieter')
                            ->relationship('hostingProvider', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
                Section::make('Server')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Anzeigename')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('hostname')
                            ->label('Hostname')
                            ->maxLength(255),
                        TextInput::make('ip_address')
                            ->label('IP-Adresse')
                            ->maxLength(45),
                        TextInput::make('region')
                            ->label('Region')
                            ->maxLength(128),
                        Select::make('status')
                            ->label('Status')
                            ->options(GermanLabels::serverStatuses())
                            ->default(ServerStatus::Active->value)
                            ->required()
                            ->native(false),
                    ]),
                Section::make('System')
                    ->columns(2)
                    ->schema([
                        TextInput::make('operating_system')
                            ->label('Betriebssystem')
                            ->maxLength(255),
                        TagsInput::make('php_versions')
                            ->label('PHP-Versionen')
                            ->placeholder('z. B. 8.2')
                            ->separator(',')
                            ->columnSpanFull(),
                    ]),
                Section::make('Vertrag & Kosten')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('contract_expires_at')
                            ->label('Vertragsende')
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
                        TextInput::make('lastpass_reference')
                            ->label('LastPass-Referenz')
                            ->maxLength(512),
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
