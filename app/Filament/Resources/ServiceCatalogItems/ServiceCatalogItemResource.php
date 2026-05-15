<?php

namespace App\Filament\Resources\ServiceCatalogItems;

use App\Filament\Resources\ServiceCatalogItems\Pages\CreateServiceCatalogItem;
use App\Filament\Resources\ServiceCatalogItems\Pages\EditServiceCatalogItem;
use App\Filament\Resources\ServiceCatalogItems\Pages\ListServiceCatalogItems;
use App\Filament\Resources\ServiceCatalogItems\Schemas\ServiceCatalogItemForm;
use App\Filament\Resources\ServiceCatalogItems\Tables\ServiceCatalogItemsTable;
use App\Models\ServiceCatalogItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ServiceCatalogItemResource extends Resource
{
    protected static ?string $model = ServiceCatalogItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Katalog-Leistung';

    protected static ?string $pluralModelLabel = 'Leistungskatalog';

    protected static string|UnitEnum|null $navigationGroup = 'Leistungskatalog';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return ServiceCatalogItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceCatalogItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceCatalogItems::route('/'),
            'create' => CreateServiceCatalogItem::route('/create'),
            'edit' => EditServiceCatalogItem::route('/{record}/edit'),
        ];
    }
}
