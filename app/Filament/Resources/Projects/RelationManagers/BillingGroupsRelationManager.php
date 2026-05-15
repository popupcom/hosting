<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Enums\BillingGroupStatus;
use App\Filament\Support\GermanLabels;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BillingGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'billingGroups';

    protected static ?string $title = 'Verrechnungsgruppen';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255),
            Select::make('billing_interval')
                ->label('Verrechnungsintervall')
                ->options(GermanLabels::serviceCatalogBillingIntervals())
                ->native(false),
            Select::make('status')
                ->label('Status')
                ->options([
                    BillingGroupStatus::Active->value => 'Aktiv',
                    BillingGroupStatus::Inactive->value => 'Inaktiv',
                ])
                ->default(BillingGroupStatus::Active->value)
                ->required()
                ->native(false),
            Textarea::make('notes')
                ->label('Notizen')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('billing_interval')
                    ->label('Intervall')
                    ->formatStateUsing(fn ($state): string => GermanLabels::serviceCatalogBillingInterval($state)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('items_count')
                    ->label('Positionen')
                    ->counts('items'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
