<?php

namespace App\Filament\Resources\CostLineItems;

use App\Filament\Resources\CostLineItems\Pages\CreateCostLineItem;
use App\Filament\Resources\CostLineItems\Pages\EditCostLineItem;
use App\Filament\Resources\CostLineItems\Pages\ListCostLineItems;
use App\Filament\Resources\CostLineItems\Schemas\CostLineItemForm;
use App\Filament\Resources\CostLineItems\Tables\CostLineItemsTable;
use App\Models\CostLineItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CostLineItemResource extends Resource
{
    protected static ?string $model = CostLineItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyEuro;

    protected static ?string $modelLabel = 'Kostenposition';

    protected static ?string $pluralModelLabel = 'Kostenpositionen';

    protected static string|UnitEnum|null $navigationGroup = 'Abrechnung';

    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return CostLineItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostLineItemsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['client', 'project', 'billable']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostLineItems::route('/'),
            'create' => CreateCostLineItem::route('/create'),
            'edit' => EditCostLineItem::route('/{record}/edit'),
        ];
    }
}
