<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\ProjectStatus;
use App\Filament\Support\GermanLabels;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Zuordnung')
                    ->columns(2)
                    ->schema([
                        Select::make('client_id')
                            ->label('Kund:in')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Section::make('Projekt')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('url')
                            ->label('URL')
                            ->url()
                            ->required()
                            ->maxLength(2048),
                        Select::make('status')
                            ->label('Status')
                            ->options(GermanLabels::projectStatuses())
                            ->default(ProjectStatus::Active->value)
                            ->required()
                            ->native(false),
                        Toggle::make('maintenance_contract')
                            ->label('Wartungsvertrag')
                            ->inline(false),
                    ]),
                Section::make('Technik')
                    ->columns(2)
                    ->schema([
                        TextInput::make('wordpress_version')
                            ->label('WordPress-Version')
                            ->maxLength(64),
                        TextInput::make('php_version')
                            ->label('PHP-Version (Projekt)')
                            ->maxLength(64),
                        TextInput::make('managewp_site_id')
                            ->label('ManageWP Site-ID')
                            ->maxLength(255),
                        TextInput::make('lastpass_reference')
                            ->label('LastPass-Referenz')
                            ->maxLength(512),
                        TextInput::make('moco_project_id')
                            ->label('Moco-Projekt-ID')
                            ->maxLength(255),
                    ]),
                Section::make('Infrastruktur & Lizenzen')
                    ->columns(1)
                    ->schema([
                        Select::make('servers')
                            ->label('Server')
                            ->relationship('servers', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Select::make('licenses')
                            ->label('Lizenzen')
                            ->relationship('licenses', 'name')
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
