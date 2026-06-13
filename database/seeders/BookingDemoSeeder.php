<?php

namespace Database\Seeders;

use App\Enums\HotelStatus;
use App\Enums\SubscriptionPlan;
use App\Models\Client;
use App\Models\Hotel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Provisions the Grünwald Resort Sölden as a fully configured demo tenant of
 * the booking SaaS, mirroring the original prototype (15 units, 6 extras,
 * 2 rates). Run with: php artisan db:seed --class=BookingDemoSeeder
 */
class BookingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::firstOrCreate(
            ['slug' => 'gruenwald-resort'],
            ['name' => 'Grünwald Resort GmbH', 'notes' => 'Demo-Mandant der Buchungs-SaaS.'],
        );

        $hotel = Hotel::updateOrCreate(
            ['slug' => 'gruenwald-resort'],
            [
                'client_id' => $client->id,
                'name' => 'Grünwald Resort Sölden',
                'status' => HotelStatus::Active,
                'location' => 'Sölden · Tirol',
                'support_phone' => '+43 650 23 66 487',
                'support_email' => 'info@gruenwald-resort.com',
                'locale' => 'de',
                'currency' => 'EUR',
                'branding' => [
                    'green' => '#7A9A5A',
                    'green_dark' => '#4D6B33',
                    'green_darker' => '#344824',
                    'green_bg' => '#f3f6ed',
                    'ink' => '#1a1a1a',
                    'accent' => '#c4a26a',
                    'font_family' => 'Mulish',
                ],
                'tracking' => [
                    'ga4_id' => 'G-DEMO0000',
                    'google_ads_id' => 'AW-0000000000',
                    'google_ads_label' => 'demoLabel',
                    'meta_pixel_id' => null,
                    'enhanced_conversions' => true,
                ],
                'legal' => [
                    'terms_url' => 'https://www.gruenwald-resort.com/agb/',
                    'cancellation_url' => 'https://www.gruenwald-resort.com/stornorichtlinien/',
                    'privacy_url' => 'https://www.gruenwald-resort.com/datenschutz/',
                ],
                'city_tax_per_person_night' => 3.50,
                'insurance_rate' => 0.055,
                'tax_percent' => 10,
                'subscription_plan' => SubscriptionPlan::Pro,
                'subscription_started_on' => now()->subMonths(2)->toDateString(),
            ],
        );

        $this->seedRates($hotel);
        $this->seedExtras($hotel);
        $this->seedUnits($hotel);
    }

    private function seedRates(Hotel $hotel): void
    {
        $rates = [
            ['key' => 'saver', 'label' => 'Spar-Vorteil', 'sublabel' => 'Nicht erstattbar · −10%', 'discount_percent' => 10, 'deposit_percent' => 100, 'is_default' => true, 'sort' => 1,
                'rules' => ['100% Anzahlung direkt bei Buchung', 'Keine kostenfreie Stornierung oder Rückerstattung möglich', 'Günstigste Rate für preisbewusste Planer']],
            ['key' => 'flex', 'label' => 'Standard', 'sublabel' => 'Flexibel', 'discount_percent' => 0, 'deposit_percent' => 50, 'is_default' => false, 'sort' => 2,
                'rules' => ['50% Anzahlung als Buchungsgarantie (nicht erstattbar)', 'Bis 61 Tage vor Anreise: 50% Stornogebühr', 'Ab 60 Tage vor Anreise: 100% Stornogebühr']],
        ];

        foreach ($rates as $rate) {
            $hotel->rates()->updateOrCreate(['key' => $rate['key']], $rate);
        }
    }

    private function seedExtras(Hotel $hotel): void
    {
        $extras = [
            ['name' => 'Frühstückskorb', 'price' => 18, 'pricing_unit' => 'per_person_per_day', 'description' => 'Frische lokale Produkte täglich an deine Tür geliefert.'],
            ['name' => 'Halbpension Panorama Alm', 'price' => 45, 'pricing_unit' => 'per_person_per_evening', 'description' => '3-Gang-Menü, Mi–Sa, regionale Küche.'],
            ['name' => 'Haustier', 'price' => 15, 'pricing_unit' => 'per_night', 'description' => 'Inklusive Welcome-Pack für deinen Hund.'],
            ['name' => 'Garagenstellplatz', 'price' => 12, 'pricing_unit' => 'per_night', 'description' => 'Sicher und überdacht, optional mit E-Ladestation.'],
            ['name' => 'Premium Wellness-Paket', 'price' => 89, 'pricing_unit' => 'once', 'description' => 'Bademantel, Sauna-Set und 2× Massage à 25 Min.'],
            ['name' => 'Aqua Dome Tageseintritt', 'price' => 32, 'pricing_unit' => 'per_person', 'description' => 'Längenfeld – Top-Therme im Ötztal, 3 Stunden Eintritt.'],
        ];

        foreach ($extras as $i => $extra) {
            $extra['slug'] = Str::slug($extra['name']);
            $extra['sort'] = $i + 1;
            $hotel->extras()->updateOrCreate(['slug' => $extra['slug']], $extra);
        }
    }

    private function seedUnits(Hotel $hotel): void
    {
        $img = 'https://www.gruenwald-resort.com/img-shared/bild-text-2800x1900';
        $units = [
            ['slug' => 'studio-budget', 'category' => 'Studios', 'name' => 'Studio Budget', 'max_pax' => 2, 'size' => '24 m²', 'rooms' => '1 Schlafzimmer · Kochnische · Terrasse · Erdgeschoss', 'description' => 'Kompaktes Studio mit allem Nötigen für entspannte Tage zu zweit.', 'price_per_night' => 119],
            ['slug' => 'studio-xs', 'category' => 'Studios', 'name' => 'Studio XS', 'max_pax' => 2, 'size' => '25 m²', 'rooms' => '1 Schlafzimmer · Kochnische · Terrasse · Südbalkon', 'description' => 'Helles Studio mit Südbalkon – ideal für Sonnenanbeter.', 'price_per_night' => 135],
            ['slug' => 'studio-s', 'category' => 'Studios', 'name' => 'Studio S', 'max_pax' => 2, 'size' => '29 m²', 'rooms' => '1 Schlafzimmer · Kochnische · Terrasse · Erdgeschoss', 'description' => 'Modernes Studio mit Terrasse für Paare, die Komfort schätzen.', 'price_per_night' => 149],
            ['slug' => 'studio-m', 'category' => 'Studios', 'name' => 'Studio M', 'max_pax' => 4, 'size' => '35 m²', 'rooms' => '1 Schlafzimmer · Kochnische · französischer Westbalkon', 'description' => 'Geräumiger Studio für 2–4 Personen mit Westbalkon und Abendsonne.', 'price_per_night' => 175],
            ['slug' => 'studio-l', 'category' => 'Studios', 'name' => 'Studio L', 'max_pax' => 6, 'size' => '55 m²', 'rooms' => '2 Schlafzimmer · Kochnische · teilweise Südbalkon', 'description' => 'Großzügiger Studio für Familien oder Freunde, bis zu 6 Personen.', 'price_per_night' => 235],
            ['slug' => 'suite-brunnenkogl', 'category' => 'Suiten', 'name' => 'Chalet Suite Brunnenkogl', 'max_pax' => 4, 'size' => '45 m²', 'rooms' => '1 Schlafzimmer · Wohnzimmer mit Schlafcouch · Kochnische · Südbalkon', 'description' => 'Hochwertige Suite mit Südbalkon und alpinem Lifestyle-Design.', 'price_per_night' => 245, 'badge' => 'Ab 21.11.2025'],
            ['slug' => 'suite-grieskogl', 'category' => 'Suiten', 'name' => 'Chalet Suite Grieskogl', 'max_pax' => 4, 'size' => '55 m²', 'rooms' => '1 Schlafzimmer · Wohnraum mit Schlafsofa · Kochnische · Süd-Ostbalkon', 'description' => 'Geräumige Suite mit Süd-Ostbalkon und panoramaartigem Bergblick.', 'price_per_night' => 275, 'badge' => 'Ab 21.11.2025'],
            ['slug' => 'apt-grieskogl', 'category' => 'Apartments', 'name' => 'Chalet Apartment Grieskogl', 'max_pax' => 6, 'size' => '60 m²', 'rooms' => '2 Schlafzimmer · Wohnraum mit Schlafsofa · Kochnische · Ostterrasse', 'description' => 'Mehrpersonen-Apartment mit Ostterrasse, perfekt für Familien.', 'price_per_night' => 295, 'badge' => 'Ab 21.11.2025'],
            ['slug' => 'apt-brunnenkogl', 'category' => 'Apartments', 'name' => 'Chalet Apartment Brunnenkogl', 'max_pax' => 7, 'size' => '68 m²', 'rooms' => '2 Schlafzimmer · Wohnraum mit Schlafsofa · Kochnische · Südterrasse', 'description' => 'Großzügiges Apartment mit Südterrasse für bis zu 7 Personen.', 'price_per_night' => 325, 'badge' => 'Ab 21.11.2025'],
            ['slug' => 'apt-gruenwald', 'category' => 'Apartments', 'name' => 'Apartment Grünwald', 'max_pax' => 12, 'size' => '170 m²', 'rooms' => '4 Schlafzimmer · Wohnküche · direkter Poolzugang', 'description' => 'XXL-Apartment mit direktem Poolzugang – einmalig für große Gruppen.', 'price_per_night' => 595],
            ['slug' => 'alpine-lodge', 'category' => 'Chalets', 'name' => 'Chalet Alpine Lodge', 'max_pax' => 6, 'size' => '110 m²', 'rooms' => '2 Schlafzimmer · Wohnküche · private Sauna · Westterrasse', 'description' => 'Privates Chalet mit eigener Sauna und Westterrasse für magische Sonnenuntergänge.', 'price_per_night' => 425],
            ['slug' => 'chalet-nederkogl', 'category' => 'Chalets', 'name' => 'Chalet Nederkogl', 'max_pax' => 8, 'size' => '100 m²', 'rooms' => '3 Schlafzimmer · Wohnküche · private Sauna · Südterrasse', 'description' => 'Großzügiges Chalet mit privater Sauna und Südterrasse für ganztägige Sonne.', 'price_per_night' => 525],
            ['slug' => 'chalet-soeldenkogl', 'category' => 'Chalets', 'name' => 'Chalet Söldenkogl', 'max_pax' => 8, 'size' => '100 m²', 'rooms' => '3 Schlafzimmer · Wohnküche · private Sauna · Terrasse', 'description' => 'Eigenständiges Chalet mit Privatsauna für Freunde oder Familien.', 'price_per_night' => 525],
            ['slug' => 'grand-chalet', 'category' => 'Chalets', 'name' => 'Grand Chalet', 'max_pax' => 14, 'size' => '240 m²', 'rooms' => '6 Schlafzimmer · Wohnküche · private Sauna · Terrasse', 'description' => 'Unser Flaggschiff: Luxuriöses Chalet auf zwei Ebenen für Gruppen bis 14 Personen.', 'price_per_night' => 895],
        ];

        foreach ($units as $i => $unit) {
            $unit['sort'] = $i + 1;
            $unit['image_url'] = $img.'/'.Str::studly($unit['slug']).'/cover.jpg';
            $hotel->units()->updateOrCreate(['slug' => $unit['slug']], $unit);
        }
    }
}
