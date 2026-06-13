<?php

namespace App\Filament\Resources\Hotels\Schemas;

use App\Enums\HotelStatus;
use App\Enums\SubscriptionPlan;
use App\Models\Client;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class HotelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Stammdaten')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText('Öffentlich erreichbar unter /book/{slug}'),
                        Select::make('client_id')
                            ->label('Kund:in')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->label('Name')->required(),
                                TextInput::make('slug')->label('Slug')->required()->alphaDash(),
                            ])
                            ->createOptionUsing(fn (array $data): int => Client::create($data)->getKey()),
                        Select::make('status')
                            ->label('Status')
                            ->options(self::enumOptions(HotelStatus::cases()))
                            ->default(HotelStatus::Onboarding->value)
                            ->required()
                            ->native(false),
                        TextInput::make('location')->label('Ort / Region')->maxLength(255),
                        TextInput::make('public_domain')->label('Eigene Domain')->placeholder('buchen.hotel.com')->maxLength(255),
                        TextInput::make('support_phone')->label('Support-Telefon')->tel()->maxLength(64),
                        TextInput::make('support_email')->label('Support-E-Mail')->email()->maxLength(255),
                        Select::make('locale')
                            ->label('Sprache')
                            ->options(['de' => 'Deutsch', 'en' => 'English', 'it' => 'Italiano'])
                            ->default('de')
                            ->native(false),
                        Select::make('currency')
                            ->label('Währung')
                            ->options(['EUR' => 'Euro', 'CHF' => 'Franken', 'USD' => 'US-Dollar'])
                            ->default('EUR')
                            ->native(false),
                    ]),

                Section::make('White-Label Branding')
                    ->description('Marken-Farben und Schrift der Buchungsstrecke. Leere Felder nutzen die Plattform-Defaults.')
                    ->columns(3)
                    ->schema([
                        ColorPicker::make('branding.green')->label('Primärfarbe'),
                        ColorPicker::make('branding.green_dark')->label('Primär dunkel'),
                        ColorPicker::make('branding.green_darker')->label('Primär dunkler'),
                        ColorPicker::make('branding.green_bg')->label('Soft-Hintergrund'),
                        ColorPicker::make('branding.ink')->label('Textfarbe / Sidebar'),
                        ColorPicker::make('branding.accent')->label('Akzent (Gold)'),
                        TextInput::make('branding.font_family')->label('Schriftfamilie (Google Font)')->placeholder('Mulish'),
                        TextInput::make('branding.logo_url')->label('Logo-URL')->url(),
                    ]),

                Section::make('Tracking & Conversion')
                    ->description('Pro Mandant eigene Tracking-IDs – Conversions landen im Werbekonto des Hotels.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('tracking.ga4_id')->label('GA4 Measurement-ID')->placeholder('G-XXXXXXX'),
                        TextInput::make('tracking.gtm_id')->label('GTM Container-ID')->placeholder('GTM-XXXXXX'),
                        TextInput::make('tracking.google_ads_id')->label('Google Ads Conversion-ID')->placeholder('AW-XXXXXXXXX'),
                        TextInput::make('tracking.google_ads_label')->label('Google Ads Conversion-Label'),
                        TextInput::make('tracking.meta_pixel_id')->label('Meta Pixel-ID'),
                        Toggle::make('tracking.enhanced_conversions')->label('Enhanced Conversions aktiv'),
                    ]),

                Section::make('Preis- & Stornologik')
                    ->columns(3)
                    ->schema([
                        TextInput::make('city_tax_per_person_night')->label('Ortstaxe p.P./Nacht')->numeric()->default(0)->prefix('€'),
                        TextInput::make('insurance_rate')->label('Versicherungssatz')->numeric()->default(0.055)->helperText('Anteil am Reisepreis, z.B. 0,055 = 5,5%'),
                        TextInput::make('tax_percent')->label('USt. %')->numeric()->default(10)->suffix('%'),
                    ]),

                Section::make('Rechtstexte (URLs)')
                    ->columns(3)
                    ->schema([
                        TextInput::make('legal.terms_url')->label('AGB')->url(),
                        TextInput::make('legal.cancellation_url')->label('Stornorichtlinien')->url(),
                        TextInput::make('legal.privacy_url')->label('Datenschutz')->url(),
                    ]),

                Section::make('PMS & Zahlung (verschlüsselt)')
                    ->description('Casablanca IBE + hobex Zugangsdaten. Werden verschlüsselt gespeichert und nie an den Browser ausgeliefert.')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('pms_config.tenant_id')->label('Casablanca Tenant-ID'),
                        TextInput::make('pms_config.ibe_context_id')->label('Casablanca IBE-Context-ID'),
                        TextInput::make('pms_config.api_key')->label('Casablanca API-Key')->password()->revealable(),
                        TextInput::make('payment_config.merchant_id')->label('hobex Merchant-ID'),
                        TextInput::make('payment_config.api_key')->label('hobex API-Key')->password()->revealable(),
                        TextInput::make('payment_config.mac_key')->label('hobex MAC-Key')->password()->revealable(),
                    ]),

                Section::make('Abonnement')
                    ->columns(3)
                    ->schema([
                        Select::make('subscription_plan')
                            ->label('Tarif')
                            ->options(self::enumOptions(SubscriptionPlan::cases(), withPrice: true))
                            ->default(SubscriptionPlan::Starter->value)
                            ->native(false),
                        DatePicker::make('subscription_started_on')->label('Abo-Start'),
                        DatePicker::make('trial_ends_on')->label('Testphase bis'),
                        Textarea::make('notes')->label('Interne Notizen')->rows(3)->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @param  array<int, HotelStatus|SubscriptionPlan>  $cases
     * @return array<string, string>
     */
    private static function enumOptions(array $cases, bool $withPrice = false): array
    {
        $options = [];
        foreach ($cases as $case) {
            $label = $case->label();
            if ($withPrice && $case instanceof SubscriptionPlan) {
                $label .= ' (€ '.$case->monthlyPrice().'/Monat)';
            }
            $options[$case->value] = $label;
        }

        return $options;
    }
}
