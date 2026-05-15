<?php

namespace App\Filament\Resources\SupportPackages;

use App\Filament\Resources\SupportPackages\Pages\CreateSupportPackage;
use App\Filament\Resources\SupportPackages\Pages\EditSupportPackage;
use App\Filament\Resources\SupportPackages\Pages\ListSupportPackages;
use App\Filament\Resources\SupportPackages\Schemas\SupportPackageForm;
use App\Filament\Resources\SupportPackages\Tables\SupportPackagesTable;
use App\Filament\Support\NavigationGroups;
use App\Models\SupportPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SupportPackageResource extends Resource
{
    protected static ?string $model = SupportPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Supportpaket (Vorlage)';

    protected static ?string $pluralModelLabel = 'Supportpaket-Vorlagen';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroups::Support;

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return SupportPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupportPackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupportPackages::route('/'),
            'create' => CreateSupportPackage::route('/create'),
            'edit' => EditSupportPackage::route('/{record}/edit'),
        ];
    }
}
