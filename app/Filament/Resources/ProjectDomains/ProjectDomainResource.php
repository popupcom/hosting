<?php

namespace App\Filament\Resources\ProjectDomains;

use App\Filament\Resources\ProjectDomains\Pages\CreateProjectDomain;
use App\Filament\Resources\ProjectDomains\Pages\EditProjectDomain;
use App\Filament\Resources\ProjectDomains\Pages\ListProjectDomains;
use App\Filament\Resources\ProjectDomains\Schemas\ProjectDomainForm;
use App\Filament\Resources\ProjectDomains\Tables\ProjectDomainsTable;
use App\Models\ProjectDomain;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProjectDomainResource extends Resource
{
    protected static ?string $model = ProjectDomain::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $recordTitleAttribute = 'domain_name';

    protected static ?string $modelLabel = 'Domain';

    protected static ?string $pluralModelLabel = 'Domains';

    protected static string|UnitEnum|null $navigationGroup = 'Kund:innen & Projekte';

    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return ProjectDomainForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectDomainsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjectDomains::route('/'),
            'create' => CreateProjectDomain::route('/create'),
            'edit' => EditProjectDomain::route('/{record}/edit'),
        ];
    }
}
