<?php

namespace App\Filament\Resources\MaintenanceHistories\Schemas;

use App\Filament\Support\GermanLabels;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaintenanceHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Eintrag')
                    ->columns(2)
                    ->schema([
                        Select::make('project_id')
                            ->label('Projekt')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('maintenance_type')
                            ->label('Art')
                            ->options(GermanLabels::maintenanceTypes())
                            ->required()
                            ->native(false),
                        TextInput::make('performed_by')
                            ->label('Durchgeführt von')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('performed_on')
                            ->label('Datum')
                            ->native(false)
                            ->required(),
                        Toggle::make('has_errors')
                            ->label('Mit Fehlern / Problemen')
                            ->inline(false),
                    ]),
                Section::make('Ergebnis')
                    ->schema([
                        Textarea::make('result')
                            ->label('Ergebnis')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notizen')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('managewp_reference')
                            ->label('ManageWP-Referenz')
                            ->maxLength(255),
                    ]),
            ]);
    }
}
