<?php

namespace App\Filament\Resources\ServiceCatalogItems\Schemas;

use App\Enums\ServiceCatalogBillingInterval;
use App\Enums\ServiceCatalogCategory;
use App\Enums\ServiceCatalogUnit;
use App\Filament\Support\GermanLabels;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ServiceCatalogItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Leistungskatalog')
                    ->description('Zentral erfassen; Zuweisung zu Projekten erfolgt unter „Projekt-Leistungen“ oder am Projekt. Moco bleibt führend für die Rechnungslegung.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->maxLength(128)
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText('Eindeutiger Schlüssel für Importe & Seeder (optional).'),
                        Select::make('category')
                            ->label('Kategorie')
                            ->options(GermanLabels::serviceCatalogCategories())
                            ->default(ServiceCatalogCategory::AdditionalService->value)
                            ->required()
                            ->native(false),
                        Select::make('unit')
                            ->label('Einheit')
                            ->options(GermanLabels::serviceCatalogUnits())
                            ->default(ServiceCatalogUnit::Piece->value)
                            ->required()
                            ->native(false),
                        TextInput::make('default_quantity')
                            ->label('Standardmenge')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->minValue(0)
                            ->step(0.0001),
                        Select::make('billing_interval')
                            ->label('Verrechnungsintervall')
                            ->options(GermanLabels::serviceCatalogBillingIntervals())
                            ->default(ServiceCatalogBillingInterval::Monthly->value)
                            ->required()
                            ->native(false),
                        TextInput::make('minimum_term_months')
                            ->label('Mindestlaufzeit (Monate)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(120)
                            ->placeholder('—')
                            ->helperText('z. B. bei Supportpaketen; leer = keine Mindestlaufzeit.'),
                        TextInput::make('sort_order')
                            ->label('Sortierung')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktiv')
                            ->default(true)
                            ->inline(false),
                    ]),
                Section::make('Preise')
                    ->columns(2)
                    ->schema([
                        TextInput::make('cost_price')
                            ->label('EK')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('€'),
                        TextInput::make('sales_price')
                            ->label('VK')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('€'),
                        Placeholder::make('margin_preview_amount')
                            ->label('Marge (Betrag)')
                            ->content(function (Get $get): string {
                                $ek = $get('cost_price');
                                $vk = $get('sales_price');
                                if ($ek === null || $ek === '' || $vk === null || $vk === '') {
                                    return '–';
                                }

                                return number_format((float) $vk - (float) $ek, 2, ',', '.').' €';
                            }),
                        Placeholder::make('margin_preview_pct')
                            ->label('Marge (%)')
                            ->content(function (Get $get): string {
                                $ek = $get('cost_price');
                                $vk = $get('sales_price');
                                if ($ek === null || $ek === '' || $vk === null || $vk === '' || (float) $ek <= 0) {
                                    return '–';
                                }

                                $pct = (((float) $vk - (float) $ek) / (float) $ek) * 100;

                                return number_format($pct, 1, ',', '.').' %';
                            }),
                    ]),
                Section::make('Details')
                    ->schema([
                        Textarea::make('description')
                            ->label('Beschreibung')
                            ->rows(5)
                            ->columnSpanFull(),
                        TextInput::make('moco_article_id')
                            ->label('Moco-Artikel-ID')
                            ->maxLength(64)
                            ->placeholder('optional'),
                        Textarea::make('notes')
                            ->label('Notizen')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
