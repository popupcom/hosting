<?php

namespace App\Filament\Resources\Hotels\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $title = 'Unterkünfte';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->label('Name')->required()->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set, string $operation) => $operation === 'create' && filled($state) ? $set('slug', Str::slug($state)) : null),
            TextInput::make('slug')->label('Slug')->required()->alphaDash(),
            TextInput::make('category')->label('Kategorie')->placeholder('Studios, Suiten, …'),
            TextInput::make('badge')->label('Badge')->placeholder('z.B. Neu'),
            TextInput::make('max_pax')->label('Max. Personen')->numeric()->default(2)->required(),
            TextInput::make('size')->label('Größe')->placeholder('45 m²'),
            TextInput::make('price_per_night')->label('Preis / Nacht')->numeric()->prefix('€')->required(),
            TextInput::make('external_id')->label('Casablanca roomTypeId'),
            TextInput::make('rooms')->label('Raumaufteilung')->columnSpanFull(),
            Textarea::make('description')->label('Beschreibung')->rows(2)->columnSpanFull(),
            TextInput::make('image_url')->label('Bild-URL')->url()->columnSpanFull(),
            TextInput::make('sort')->label('Reihenfolge')->numeric()->default(0),
            Toggle::make('is_active')->label('Aktiv')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('category')->label('Kategorie')->badge(),
                TextColumn::make('max_pax')->label('Pers.')->alignCenter(),
                TextColumn::make('price_per_night')->label('€/Nacht')->money('EUR')->sortable(),
                IconColumn::make('is_active')->label('Aktiv')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
