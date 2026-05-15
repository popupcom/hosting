<?php

namespace App\Filament\Resources\LicenseProducts;

use App\Filament\Resources\LicenseProducts\Pages\CreateLicenseProduct;
use App\Filament\Resources\LicenseProducts\Pages\EditLicenseProduct;
use App\Filament\Resources\LicenseProducts\Pages\ListLicenseProducts;
use App\Filament\Resources\LicenseProducts\Schemas\LicenseProductForm;
use App\Filament\Resources\LicenseProducts\Tables\LicenseProductsTable;
use App\Models\LicenseProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LicenseProductResource extends Resource
{
    protected static ?string $model = LicenseProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Lizenzprodukt';

    protected static ?string $pluralModelLabel = 'Lizenzprodukte';

    protected static string|UnitEnum|null $navigationGroup = 'Lizenzen & Support';

    protected static ?int $navigationSort = 48;

    public static function form(Schema $schema): Schema
    {
        return LicenseProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LicenseProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LicenseAssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLicenseProducts::route('/'),
            'create' => CreateLicenseProduct::route('/create'),
            'edit' => EditLicenseProduct::route('/{record}/edit'),
        ];
    }
}
