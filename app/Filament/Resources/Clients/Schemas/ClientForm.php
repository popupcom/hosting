<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Enums\ClientStatus;
use App\Filament\Support\GermanLabels;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Stammdaten')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->alphaDash(),
                        TextInput::make('company')
                            ->label('Firma')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options(GermanLabels::clientStatuses())
                            ->default(ClientStatus::Active->value)
                            ->required()
                            ->native(false),
                    ]),
                Section::make('Kontakt')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('E-Mail')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(255),
                        Textarea::make('address')
                            ->label('Adresse')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
                Section::make('Sonstiges')
                    ->columns(2)
                    ->schema([
                        TextInput::make('moco_customer_id')
                            ->label('Moco-Kunden-ID')
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label('Notizen')
                            ->columnSpanFull()
                            ->rows(4),
                    ]),
            ]);
    }
}
