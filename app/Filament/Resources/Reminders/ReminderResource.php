<?php

namespace App\Filament\Resources\Reminders;

use App\Filament\Resources\Reminders\Pages\CreateReminder;
use App\Filament\Resources\Reminders\Pages\EditReminder;
use App\Filament\Resources\Reminders\Pages\ListReminders;
use App\Filament\Resources\Reminders\Schemas\ReminderForm;
use App\Filament\Resources\Reminders\Tables\RemindersTable;
use App\Filament\Support\NavigationGroups;
use App\Models\Reminder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReminderResource extends Resource
{
    protected static ?string $model = Reminder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'ToDos';

    protected static ?string $modelLabel = 'ToDo';

    protected static ?string $pluralModelLabel = 'ToDos';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroups::Support;

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return ReminderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RemindersTable::configure($table)
            ->modifyQueryUsing(fn ($query) => $query->with(['remindable', 'assignedUser']));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReminders::route('/'),
            'create' => CreateReminder::route('/create'),
            'edit' => EditReminder::route('/{record}/edit'),
        ];
    }
}
