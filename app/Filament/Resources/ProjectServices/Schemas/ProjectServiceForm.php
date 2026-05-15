<?php

namespace App\Filament\Resources\ProjectServices\Schemas;

use App\Enums\ProjectServiceMocoSyncStatus;
use App\Enums\ProjectServiceStatus;
use App\Filament\Support\GermanLabels;
use App\Models\BillingGroup;
use App\Models\ServiceCatalogItem;
use App\Support\Money;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

final class ProjectServiceForm
{
    public static function configure(Schema $schema, bool $includeProjectSelect = true, ?int $fixedProjectId = null): Schema
    {
        $sections = [];

        if ($includeProjectSelect) {
            $sections[] = Section::make('Zuordnung')
                ->columns(2)
                ->schema([
                    Select::make('project_id')
                        ->label('Projekt')
                        ->relationship(
                            name: 'project',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                        )
                        ->default($fixedProjectId)
                        ->disabled($fixedProjectId !== null)
                        ->dehydrated()
                        ->searchable()
                        ->preload()
                        ->required(),
                ]);
        }

        $sections[] = Section::make('Leistung aus Katalog')
            ->columns(2)
            ->schema([
                Select::make('service_catalog_item_id')
                    ->label('Leistungskatalog')
                    ->relationship(
                        name: 'serviceCatalogItem',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->activeCatalog()->orderBy('sort_order')->orderBy('name'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (ServiceCatalogItem $record): string => "#{$record->getKey()} — {$record->name}")
                    ->searchable()
                    ->preload()
                    ->live()
                    ->helperText('Beim ersten Speichern werden Katalogwerte als Snapshot übernommen. Spätere Katalog-Änderungen überschreiben diese Zuweisung nicht.')
                    ->afterStateUpdated(function (callable $set, $state, $livewire): void {
                        if (blank($state)) {
                            return;
                        }
                        $item = ServiceCatalogItem::query()->find($state);
                        if ($item === null) {
                            return;
                        }
                        if (! method_exists($livewire, 'getRecord') || $livewire->getRecord() === null) {
                            $set('quantity', $item->default_quantity ?? 1);
                        }
                    })
                    ->required(),
                TextInput::make('custom_name')
                    ->label('Individueller Name')
                    ->maxLength(255)
                    ->helperText('Leer = Snapshot-Name'),
                TextInput::make('quantity')
                    ->label('Menge')
                    ->numeric()
                    ->default(1)
                    ->minValue(0)
                    ->step(0.0001)
                    ->required(),
                TextInput::make('custom_quantity')
                    ->label('Individuelle Menge')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.0001)
                    ->helperText('Leer = Menge oben'),
                Textarea::make('custom_description')
                    ->label('Individuelle Beschreibung')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);

        $sections[] = Section::make('Preise & Verrechnung')
            ->columns(2)
            ->schema([
                TextInput::make('custom_cost_price')
                    ->label('Individueller EK')
                    ->numeric()
                    ->step(0.01)
                    ->suffix('€')
                    ->helperText('Leer = EK-Snapshot'),
                TextInput::make('custom_sales_price')
                    ->label('Individueller VK')
                    ->numeric()
                    ->step(0.01)
                    ->suffix('€')
                    ->helperText('Leer = VK-Snapshot'),
                Placeholder::make('effective_ek_hint')
                    ->label('EK (effektiv)')
                    ->content(fn (Get $get): string => self::effectiveHint($get, 'cost')),
                Placeholder::make('effective_vk_hint')
                    ->label('VK (effektiv)')
                    ->content(fn (Get $get): string => self::effectiveHint($get, 'sales')),
                Select::make('custom_billing_interval')
                    ->label('Individuelles Intervall')
                    ->options(GermanLabels::serviceCatalogBillingIntervals())
                    ->placeholder('Wie Snapshot')
                    ->native(false),
                Select::make('billing_group_id')
                    ->label('Verrechnungsgruppe')
                    ->options(function (Get $get) use ($fixedProjectId): array {
                        $projectId = $fixedProjectId ?? $get('project_id');
                        if (blank($projectId)) {
                            return [];
                        }

                        return BillingGroup::query()
                            ->where('project_id', $projectId)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->placeholder('Einzeln verrechnen')
                    ->native(false)
                    ->helperText('Gemeinsame Verrechnung mit anderen Leistungen/Lizenzen des Projekts.'),
            ]);

        $sections[] = Section::make('Laufzeit & Kündigung')
            ->columns(2)
            ->schema([
                DatePicker::make('start_date')->label('Startdatum')->native(false),
                DatePicker::make('end_date')->label('Enddatum')->native(false),
                TextInput::make('minimum_term_months')->label('Mindestlaufzeit (Monate)')->numeric()->minValue(0),
                DatePicker::make('next_renewal_date')->label('Nächste Verlängerung')->native(false),
                DatePicker::make('cancellation_notice_until')->label('Kündigungsfrist bis')->native(false),
                DatePicker::make('cancellation_date')->label('Kündigungsdatum / Enddatum')->native(false),
                Toggle::make('do_not_renew')->label('Nicht verlängern')->live(),
                Toggle::make('renews_automatically')->label('Verlängert sich automatisch'),
                Textarea::make('cancellation_reason')
                    ->label('Kündigungsgrund')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);

        $sections[] = Section::make('Status & Moco')
            ->columns(2)
            ->schema([
                Select::make('status')
                    ->label('Status')
                    ->options(GermanLabels::projectServiceStatuses())
                    ->default(ProjectServiceStatus::Active->value)
                    ->required()
                    ->native(false),
                Select::make('moco_sync_status')
                    ->label('Moco-Sync-Status')
                    ->options(GermanLabels::projectServiceMocoSyncStatuses())
                    ->default(ProjectServiceMocoSyncStatus::NotSynced->value)
                    ->required()
                    ->native(false),
                TextInput::make('moco_invoice_reference')
                    ->label('Moco-Referenz')
                    ->maxLength(255)
                    ->columnSpanFull(),
                DatePicker::make('price_change_effective_from')
                    ->label('Preisänderung ab')
                    ->native(false),
            ]);

        $sections[] = Section::make('Notizen')
            ->schema([
                Textarea::make('notes')->label('Notizen')->rows(3),
            ]);

        return $schema->components($sections);
    }

    private static function effectiveHint(Get $get, string $kind): string
    {
        $customField = $kind === 'cost' ? 'custom_cost_price' : 'custom_sales_price';
        $snapshotField = $kind === 'cost' ? 'cost_price_snapshot' : 'sales_price_snapshot';
        $custom = $get($customField);
        if ($custom !== null && $custom !== '') {
            return Money::euro($custom).' (individuell)';
        }
        $snap = $get($snapshotField);
        if ($snap !== null && $snap !== '') {
            return Money::euro($snap).' (Snapshot)';
        }

        return '— (wird beim Speichern aus Katalog übernommen, falls neu)';
    }
}
