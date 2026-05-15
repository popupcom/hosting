<?php

namespace App\Filament\Resources\SupportPackages\Schemas;

use App\Enums\ServiceCatalogBillingInterval;
use App\Enums\ServiceCatalogCategory;
use App\Models\ServiceCatalogItem;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class SupportPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Stammdaten')
                    ->description('Preise kommen aus dem verknüpften Leistungskatalog — nicht doppelt pflegen.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        Select::make('service_catalog_item_id')
                            ->label('Leistung im Katalog')
                            ->relationship(
                                name: 'serviceCatalogItem',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query
                                    ->where('category', ServiceCatalogCategory::SupportPackage)
                                    ->where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->orderBy('name'),
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->native(false),
                        Placeholder::make('catalog_monthly_vk')
                            ->label('Monatlicher VK (Katalog)')
                            ->content(fn (Get $get): string => self::formatCatalogPrice($get('service_catalog_item_id'), monthly: true)),
                        Placeholder::make('catalog_yearly_vk')
                            ->label('Jährlicher VK (Katalog × 12)')
                            ->content(fn (Get $get): string => self::formatCatalogPrice($get('service_catalog_item_id'), monthly: false)),
                        TextInput::make('monthly_minutes')
                            ->label('Monatliche Minuten')
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->suffix('Min.')
                            ->live(),
                        TextInput::make('yearly_hours')
                            ->label('Jährliche Stunden')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->suffix('Std.')
                            ->helperText('Berechnung: (monatliche Minuten × 12) ÷ 60'),
                        TextInput::make('update_frequency')
                            ->label('Update-Intervall')
                            ->maxLength(255),
                        TextInput::make('response_time')
                            ->label('Reaktionszeit')
                            ->maxLength(255),
                        TextInput::make('minimum_term_months')
                            ->label('Mindestlaufzeit (Monate)')
                            ->numeric()
                            ->minValue(0)
                            ->default(6)
                            ->required(),
                        Select::make('billing_interval')
                            ->label('Abrechnungsintervall')
                            ->options([
                                ServiceCatalogBillingInterval::Yearly->value => 'Jährlich',
                                ServiceCatalogBillingInterval::Monthly->value => 'Monatlich',
                            ])
                            ->default(ServiceCatalogBillingInterval::Yearly->value)
                            ->required()
                            ->native(false),
                        Toggle::make('bill_yearly_in_advance')
                            ->label('Jährlich im Voraus verrechnen')
                            ->default(true),
                        Toggle::make('is_active')
                            ->label('Aktiv')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('Sortierung')
                            ->numeric()
                            ->default(0),
                    ]),
                Section::make('Enthaltene Leistungen (Technik)')
                    ->description('Für Technik und Wartungsplanung sichtbar.')
                    ->columns(3)
                    ->schema(self::technicalCheckboxes()),
                Section::make('Beschreibung')
                    ->schema([
                        Textarea::make('included_services')
                            ->label('Enthaltene Leistungen (Freitext)')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Beschreibung')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notizen')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return list<Checkbox>
     */
    private static function technicalCheckboxes(): array
    {
        $fields = [
            'includes_daily_backups' => 'Tägliche Backups',
            'includes_plugin_updates' => 'Plugin-Updates',
            'includes_link_monitoring' => 'Link-Monitoring',
            'includes_security_checks' => 'Security-Checks',
            'includes_uptime_monitoring' => 'Uptime-Monitoring',
            'includes_wordpress_core_update' => 'WordPress-Core-Update',
            'includes_theme_update' => 'Theme-Update',
            'includes_performance_check' => 'Performance-Check',
            'includes_multisite' => 'Multisite',
            'includes_custom_websites' => 'Custom Websites',
            'includes_online_shops' => 'Onlineshops',
        ];

        return array_map(
            fn (string $field, string $label): Checkbox => Checkbox::make($field)->label($label),
            array_keys($fields),
            array_values($fields),
        );
    }

    private static function formatCatalogPrice(mixed $catalogItemId, bool $monthly): string
    {
        if (blank($catalogItemId)) {
            return '—';
        }

        $item = ServiceCatalogItem::query()->find($catalogItemId);
        if ($item?->sales_price === null) {
            return '—';
        }

        $amount = (float) $item->sales_price;

        if (! $monthly) {
            $amount *= 12;
        }

        return number_format($amount, 2, ',', '.').' €';
    }
}
