<?php

namespace App\Filament\Resources\ProjectServices;

use App\Filament\Resources\ProjectServices\Pages\CreateProjectService;
use App\Filament\Resources\ProjectServices\Pages\EditProjectService;
use App\Filament\Resources\ProjectServices\Pages\ListProjectServices;
use App\Filament\Resources\ProjectServices\Schemas\ProjectServiceForm;
use App\Filament\Resources\ProjectServices\Tables\ProjectServicesTable;
use App\Models\ProjectService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProjectServiceResource extends Resource
{
    protected static ?string $model = ProjectService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $modelLabel = 'Projekt-Leistung';

    protected static ?string $pluralModelLabel = 'Projekt-Leistungen Übersicht';

    protected static ?string $navigationLabel = 'Projekt-Leistungen';

    protected static string|UnitEnum|null $navigationGroup = 'Leistungskatalog';

    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return ProjectServiceForm::configure($schema, true);
    }

    public static function table(Table $table): Table
    {
        return ProjectServicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjectServices::route('/'),
            'create' => CreateProjectService::route('/create'),
            'edit' => EditProjectService::route('/{record}/edit'),
        ];
    }
}
