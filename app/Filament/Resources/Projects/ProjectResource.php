<?php

namespace App\Filament\Resources\Projects;

use App\Filament\Resources\Projects\Pages\AddProjectServices;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Projekt / Website';

    protected static ?string $pluralModelLabel = 'Projekte / Websites';

    protected static string|UnitEnum|null $navigationGroup = 'Kund:innen & Projekte';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProjectServicesRelationManager::class,
            RelationManagers\ProjectLicensesRelationManager::class,
            RelationManagers\ProjectSupportPackagesRelationManager::class,
            RelationManagers\BillingGroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
            'add-services' => AddProjectServices::route('/{record}/leistungen-hinzufuegen'),
        ];
    }
}
