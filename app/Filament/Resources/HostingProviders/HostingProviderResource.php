<?php

namespace App\Filament\Resources\HostingProviders;

use App\Filament\Resources\HostingProviders\Pages\CreateHostingProvider;
use App\Filament\Resources\HostingProviders\Pages\EditHostingProvider;
use App\Filament\Resources\HostingProviders\Pages\ListHostingProviders;
use App\Filament\Resources\HostingProviders\Schemas\HostingProviderForm;
use App\Filament\Resources\HostingProviders\Tables\HostingProvidersTable;
use App\Models\HostingProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HostingProviderResource extends Resource
{
    protected static ?string $model = HostingProvider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCloud;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Hosting-Anbieter';

    protected static ?string $pluralModelLabel = 'Hosting-Anbieter';

    protected static string|UnitEnum|null $navigationGroup = 'Infrastruktur';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return HostingProviderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HostingProvidersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHostingProviders::route('/'),
            'create' => CreateHostingProvider::route('/create'),
            'edit' => EditHostingProvider::route('/{record}/edit'),
        ];
    }
}
