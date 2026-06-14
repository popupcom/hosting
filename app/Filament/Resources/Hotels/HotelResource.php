<?php

namespace App\Filament\Resources\Hotels;

use App\Filament\Resources\Hotels\Pages\CreateHotel;
use App\Filament\Resources\Hotels\Pages\EditHotel;
use App\Filament\Resources\Hotels\Pages\ListHotels;
use App\Filament\Resources\Hotels\RelationManagers\ExtrasRelationManager;
use App\Filament\Resources\Hotels\RelationManagers\RatesRelationManager;
use App\Filament\Resources\Hotels\RelationManagers\UnitsRelationManager;
use App\Filament\Resources\Hotels\Schemas\HotelForm;
use App\Filament\Resources\Hotels\Tables\HotelsTable;
use App\Models\Hotel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HotelResource extends Resource
{
    protected static ?string $model = Hotel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Hotel';

    protected static ?string $pluralModelLabel = 'Hotels';

    protected static string|UnitEnum|null $navigationGroup = 'Buchungs-SaaS';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return HotelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UnitsRelationManager::class,
            RatesRelationManager::class,
            ExtrasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHotels::route('/'),
            'create' => CreateHotel::route('/create'),
            'edit' => EditHotel::route('/{record}/edit'),
        ];
    }
}
