<?php

namespace App\Filament\Resources\Reminders\Schemas;

use App\Enums\ReminderStatus;
use App\Filament\Support\GermanLabels;
use App\Models\MaintenanceHistory;
use App\Models\ProjectDomain;
use App\Models\Server;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReminderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ToDo')
                    ->columns(2)
                    ->schema([
                        Select::make('remindable_type')
                            ->label('Bezug')
                            ->options(GermanLabels::todoRemindableTypes())
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (callable $set): mixed => $set('remindable_id', null)),
                        Select::make('remindable_id')
                            ->label('Referenz')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->options(fn (callable $get): array => self::remindableOptions($get('remindable_type'))),
                        Select::make('assigned_user_id')
                            ->label('Zugewiesen an')
                            ->relationship(
                                name: 'assignedUser',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('name'),
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Nicht zugewiesen'),
                        DatePicker::make('reminder_at')
                            ->label('Fällig am')
                            ->native(false)
                            ->required(),
                        Select::make('status')
                            ->label('ToDo Status')
                            ->options(GermanLabels::todoStatuses())
                            ->default(ReminderStatus::Pending->value)
                            ->required()
                            ->native(false),
                        Toggle::make('is_done')
                            ->label('ToDo erledigt')
                            ->inline(false),
                    ]),
                Section::make('Nachricht')
                    ->schema([
                        Textarea::make('message')
                            ->label('Nachricht')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array<int|string, string>
     */
    private static function remindableOptions(?string $type): array
    {
        if ($type === null || $type === '') {
            return [];
        }

        return match ($type) {
            ProjectDomain::class => ProjectDomain::query()
                ->orderBy('domain_name')
                ->pluck('domain_name', 'id')
                ->all(),
            Server::class => Server::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),
            MaintenanceHistory::class => MaintenanceHistory::query()
                ->with('project')
                ->orderByDesc('performed_on')
                ->limit(200)
                ->get()
                ->mapWithKeys(fn (MaintenanceHistory $history): array => [
                    $history->id => ($history->project?->name ?? 'Wartung').' · '.$history->performed_on?->format('d.m.Y'),
                ])
                ->all(),
            default => [],
        };
    }
}
