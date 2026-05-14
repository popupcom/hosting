<?php

namespace App\Filament\Resources\CostLineItems\Schemas;

use App\Enums\CostLineItemType;
use App\Enums\MocoSyncStatus;
use App\Filament\Support\GermanLabels;
use App\Models\License;
use App\Models\ProjectDomain;
use App\Models\Server;
use App\Models\SupportPackage;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type as MorphToSelectType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CostLineItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Zuordnung')
                    ->columns(2)
                    ->schema([
                        Select::make('client_id')
                            ->label('Kund:in')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('project_id', null))
                            ->required(),
                        Select::make('project_id')
                            ->label('Projekt')
                            ->relationship(
                                'project',
                                'name',
                                fn (Builder $query, Get $get): Builder => $get('client_id')
                                    ? $query->where('client_id', $get('client_id'))
                                    : $query->whereRaw('0 = 1'),
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                    ]),
                Section::make('Position')
                    ->columns(2)
                    ->schema([
                        Select::make('line_type')
                            ->label('Art')
                            ->options(GermanLabels::costLineItemTypes())
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if ($state === CostLineItemType::AdditionalService->value) {
                                    $set('billable_type', null);
                                    $set('billable_id', null);
                                }
                            }),
                        MorphToSelect::make('billable')
                            ->label('Abrechnungsobjekt')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => in_array((string) $get('line_type'), [
                                CostLineItemType::Domain->value,
                                CostLineItemType::Hosting->value,
                                CostLineItemType::License->value,
                                CostLineItemType::SupportPackage->value,
                            ], true))
                            ->required(fn (Get $get): bool => in_array((string) $get('line_type'), [
                                CostLineItemType::Domain->value,
                                CostLineItemType::Hosting->value,
                                CostLineItemType::License->value,
                                CostLineItemType::SupportPackage->value,
                            ], true))
                            ->types([
                                MorphToSelectType::make(ProjectDomain::class)
                                    ->titleAttribute('domain_name')
                                    ->modifyOptionsQueryUsing(function (Builder $query, Get $get): Builder {
                                        if (! filled($get('project_id'))) {
                                            return $query->whereRaw('0 = 1');
                                        }

                                        return $query->where('project_id', $get('project_id'))->orderBy('domain_name');
                                    }),
                                MorphToSelectType::make(Server::class)
                                    ->titleAttribute('name')
                                    ->modifyOptionsQueryUsing(function (Builder $query, Get $get): Builder {
                                        if (! filled($get('project_id'))) {
                                            return $query->whereRaw('0 = 1');
                                        }

                                        return $query->whereHas(
                                            'projects',
                                            fn (Builder $q) => $q->where('projects.id', $get('project_id')),
                                        )->orderBy('name');
                                    }),
                                MorphToSelectType::make(License::class)
                                    ->titleAttribute('name')
                                    ->modifyOptionsQueryUsing(function (Builder $query, Get $get): Builder {
                                        if (! filled($get('project_id'))) {
                                            return $query->whereRaw('0 = 1');
                                        }

                                        return $query->whereHas(
                                            'projects',
                                            fn (Builder $q) => $q->where('projects.id', $get('project_id')),
                                        )->orderBy('name');
                                    }),
                                MorphToSelectType::make(SupportPackage::class)
                                    ->titleAttribute('name')
                                    ->modifyOptionsQueryUsing(function (Builder $query, Get $get): Builder {
                                        if (! filled($get('project_id'))) {
                                            return $query->whereRaw('0 = 1');
                                        }

                                        return $query->where('project_id', $get('project_id'))->orderBy('name');
                                    }),
                            ]),
                    ]),
                Section::make('Beträge & Abrechnung')
                    ->columns(2)
                    ->schema([
                        TextInput::make('cost_price')
                            ->label('Einkaufspreis')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('€'),
                        TextInput::make('selling_price')
                            ->label('Verkaufspreis')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('€'),
                        TextInput::make('billing_interval')
                            ->label('Abrechnungsintervall')
                            ->maxLength(32),
                        Select::make('moco_sync_status')
                            ->label('Moco-Sync')
                            ->options(GermanLabels::mocoSyncStatuses())
                            ->default(MocoSyncStatus::Pending->value)
                            ->required()
                            ->native(false),
                        Toggle::make('is_active')
                            ->label('Aktiv')
                            ->default(true)
                            ->inline(false),
                    ]),
                Section::make('Notizen')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notizen')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
