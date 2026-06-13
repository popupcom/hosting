<?php

namespace App\Filament\Resources\Hotels\RelationManagers;

use App\Enums\ExtraPricingUnit;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ExtrasRelationManager extends RelationManager
{
    protected static string $relationship = 'extras';

    protected static ?string $title = 'Zusatzleistungen';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->label('Name')->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set, string $operation) => $operation === 'create' && filled($state) ? $set('slug', Str::slug($state)) : null),
            TextInput::make('slug')->label('Slug')->required()->alphaDash(),
            TextInput::make('price')->label('Preis')->numeric()->prefix('€')->required(),
            Select::make('pricing_unit')->label('Abrechnung')
                ->options(collect(ExtraPricingUnit::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all())
                ->default(ExtraPricingUnit::Once->value)
                ->required()->native(false),
            Textarea::make('description')->label('Beschreibung')->rows(2)->columnSpanFull(),
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
                TextColumn::make('price')->label('Preis')->money('EUR'),
                TextColumn::make('pricing_unit')->label('Abrechnung')
                    ->formatStateUsing(fn (ExtraPricingUnit $state): string => $state->label())->badge(),
                IconColumn::make('is_active')->label('Aktiv')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
