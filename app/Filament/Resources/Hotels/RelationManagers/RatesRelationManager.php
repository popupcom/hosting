<?php

namespace App\Filament\Resources\Hotels\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RatesRelationManager extends RelationManager
{
    protected static string $relationship = 'rates';

    protected static ?string $title = 'Raten';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('key')->label('Schlüssel')->required()->alphaDash()->placeholder('saver / flex'),
            TextInput::make('label')->label('Bezeichnung')->required(),
            TextInput::make('sublabel')->label('Zusatz')->placeholder('Nicht erstattbar · −10%'),
            TextInput::make('discount_percent')->label('Rabatt %')->numeric()->default(0)->suffix('%')->required(),
            TextInput::make('deposit_percent')->label('Anzahlung %')->numeric()->default(100)->suffix('%')->required(),
            TextInput::make('sort')->label('Reihenfolge')->numeric()->default(0),
            TagsInput::make('rules')->label('Bedingungen')->columnSpanFull()->helperText('Enter drücken pro Zeile'),
            Toggle::make('is_default')->label('Standard-Rate'),
            Toggle::make('is_active')->label('Aktiv')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('label')->label('Bezeichnung')->searchable(),
                TextColumn::make('discount_percent')->label('Rabatt')->suffix('%'),
                TextColumn::make('deposit_percent')->label('Anzahlung')->suffix('%'),
                IconColumn::make('is_default')->label('Standard')->boolean(),
                IconColumn::make('is_active')->label('Aktiv')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
