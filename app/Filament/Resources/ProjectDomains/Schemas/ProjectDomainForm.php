<?php

namespace App\Filament\Resources\ProjectDomains\Schemas;

use App\Enums\ProjectDomainStatus;
use App\Filament\Support\GermanLabels;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectDomainForm
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
                Section::make('Domain')
                    ->columns(2)
                    ->schema([
                        TextInput::make('domain_name')
                            ->label('Domainname')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options(GermanLabels::projectDomainStatuses())
                            ->default(ProjectDomainStatus::Active->value)
                            ->required()
                            ->native(false),
                        TextInput::make('registrar')
                            ->label('Registrar')
                            ->maxLength(255),
                        TextInput::make('hosting_provider')
                            ->label('Hosting (Freitext)')
                            ->maxLength(255),
                        TextInput::make('autodns_id')
                            ->label('AutoDNS-ID')
                            ->maxLength(255),
                    ]),
                Section::make('DNS')
                    ->schema([
                        Textarea::make('dns_zone')
                            ->label('DNS-Zone')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('nameservers')
                            ->label('Nameserver')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Laufzeit & Kosten')
                    ->columns(2)
                    ->schema([
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
                            ->label('ToDo fällig am')
                            ->native(false),
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
