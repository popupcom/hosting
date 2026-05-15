<?php

namespace Database\Seeders;

use App\Enums\ServiceCatalogBillingInterval;
use App\Enums\ServiceCatalogCategory;
use App\Enums\ServiceCatalogUnit;
use App\Models\ServiceCatalogItem;
use Illuminate\Database\Seeder;

/**
 * Zentraler Leistungskatalog — VK-Preise und Metadaten.
 *
 * Einträge werden per {@see ServiceCatalogItem::$slug} idempotent angelegt/aktualisiert.
 * Alte Demo-Zeilen „(Beispiel)“ ohne verknüpfte Projekt-Leistungen werden entfernt.
 */
class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        ServiceCatalogItem::query()
            ->where('name', 'like', '%(Beispiel)%')
            ->whereDoesntHave('projectServices')
            ->delete();

        $defaults = [
            'description' => null,
            'default_quantity' => 1,
            'cost_price' => null,
            'is_active' => true,
            'notes' => null,
            'moco_article_id' => null,
            'minimum_term_months' => null,
        ];

        foreach (self::definitions() as $sortOrder => $row) {
            $slug = (string) ($row['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $payload = array_merge($defaults, $row, [
                'sort_order' => $sortOrder,
                'category' => $row['category'] instanceof ServiceCatalogCategory
                    ? $row['category']->value
                    : (string) $row['category'],
                'unit' => ($row['unit'] ?? ServiceCatalogUnit::Month)->value,
                'billing_interval' => ($row['billing_interval'] ?? ServiceCatalogBillingInterval::Monthly)->value,
                'sales_price' => isset($row['sales_price']) ? self::decimal($row['sales_price']) : null,
            ]);

            unset($payload['slug']);

            ServiceCatalogItem::query()->updateOrCreate(
                ['slug' => $slug],
                array_merge($payload, ['slug' => $slug]),
            );
        }
    }

    private static function decimal(float|int|string $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function definitions(): array
    {
        return array_merge(
            self::hostingLitespeed(),
            self::hostingTiers(),
            self::toolsSaas(),
            self::ssl(),
            self::licenses(),
            self::supportPackages(),
            self::qrCode(),
            self::mailExchange(),
            self::domains(),
            self::additionalServices(),
            self::storageAddons(),
            self::issuu(),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function hostingLitespeed(): array
    {
        $c = ServiceCatalogCategory::Hosting;
        $u = ServiceCatalogUnit::Month;
        $b = ServiceCatalogBillingInterval::Monthly;

        return [
            ['slug' => 'hosting-litespeed-5gb', 'name' => 'popup Litespeed 5 GB', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 39.90],
            ['slug' => 'hosting-litespeed-10gb', 'name' => 'popup Litespeed 10 GB', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 49.90],
            ['slug' => 'hosting-litespeed-15gb', 'name' => 'popup Litespeed 15 GB', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 79.90],
            ['slug' => 'hosting-litespeed-20gb', 'name' => 'popup Litespeed 20 GB', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 99.90],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function hostingTiers(): array
    {
        $c = ServiceCatalogCategory::Hosting;
        $u = ServiceCatalogUnit::Month;
        $b = ServiceCatalogBillingInterval::Monthly;

        return [
            ['slug' => 'hosting-tier-small', 'name' => 'Hosting small', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 4.90],
            ['slug' => 'hosting-tier-medium', 'name' => 'Hosting medium', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 9.90],
            ['slug' => 'hosting-tier-large', 'name' => 'Hosting large', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 14.90],
            ['slug' => 'hosting-tier-xlarge', 'name' => 'Hosting X-large', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 19.90],
            ['slug' => 'hosting-tier-xxlarge', 'name' => 'Hosting XX-large', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 24.90],
            ['slug' => 'hosting-tier-xxxlarge', 'name' => 'Hosting XXX-large', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 34.90],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function toolsSaas(): array
    {
        $c = ServiceCatalogCategory::ToolSaas;
        $u = ServiceCatalogUnit::Month;
        $b = ServiceCatalogBillingInterval::Monthly;

        return [
            ['slug' => 'tool-matomo', 'name' => 'Matomo Analysetool', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 10.00],
            ['slug' => 'tool-social-wall-one-stream', 'name' => 'Social Wall ein Stream', 'category' => $c, 'unit' => $u, 'billing_interval' => ServiceCatalogBillingInterval::FlatRate, 'sales_price' => 18.00],
            ['slug' => 'tool-lead-funnel', 'name' => 'Lead Funnel', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 20.00],
            ['slug' => 'tool-typeform-leadfunnel', 'name' => 'Typeform Leadfunnel-Tool', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 20.00],
            ['slug' => 'tool-backup-monitoring', 'name' => 'Website Backup & Monitoring', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 5.00],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function ssl(): array
    {
        $c = ServiceCatalogCategory::Ssl;
        $u = ServiceCatalogUnit::Year;
        $b = ServiceCatalogBillingInterval::Yearly;

        return [
            ['slug' => 'ssl-standard-yearly', 'name' => 'SSL Zertifikat Jahresgebühr', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 15.00],
            ['slug' => 'ssl-wildcard-yearly', 'name' => 'SSL Wildcard Zertifikat', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 120.00],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function licenses(): array
    {
        $c = ServiceCatalogCategory::License;
        $year = ServiceCatalogUnit::Year;
        $piece = ServiceCatalogUnit::Piece;
        $yearly = ServiceCatalogBillingInterval::Yearly;
        $oneTime = ServiceCatalogBillingInterval::OneTime;

        return [
            ['slug' => 'license-borlabs-cookie-yearly', 'name' => 'Borlabs Cookie Opt In Jahresgebühr', 'category' => $c, 'unit' => $year, 'billing_interval' => $yearly, 'sales_price' => 19.90],
            ['slug' => 'license-cookiebot-yearly', 'name' => 'Cookiebot Cookie Opt In Tool', 'category' => $c, 'unit' => $year, 'billing_interval' => $yearly, 'sales_price' => 27.50],
            ['slug' => 'license-aioseo-yearly', 'name' => 'AIO SEO Jahresgebühr', 'category' => $c, 'unit' => $year, 'billing_interval' => $yearly, 'sales_price' => 29.00],
            ['slug' => 'license-omgf-plugin', 'name' => 'OMGF Plugin', 'category' => $c, 'unit' => $piece, 'billing_interval' => $oneTime, 'sales_price' => 5.00],
            ['slug' => 'license-wordpress-theme', 'name' => 'WordPress Theme', 'category' => $c, 'unit' => $piece, 'billing_interval' => $oneTime, 'sales_price' => 79.00],
            ['slug' => 'license-datenschutz-doc', 'name' => 'Datenschutzerklärung Lizenzkosten', 'category' => $c, 'unit' => $piece, 'billing_interval' => $oneTime, 'sales_price' => 190.00],
            ['slug' => 'license-polylang-yearly', 'name' => 'Polylang Sprachplugin WordPress', 'category' => $c, 'unit' => $year, 'billing_interval' => $yearly, 'sales_price' => 99.00],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function supportPackages(): array
    {
        $c = ServiceCatalogCategory::SupportPackage;
        $u = ServiceCatalogUnit::Month;
        $b = ServiceCatalogBillingInterval::Monthly;

        return [
            ['slug' => 'support-wp-lite', 'name' => 'WordPress Support Paket LITE', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 29.90, 'minimum_term_months' => 1],
            ['slug' => 'support-wp-standard', 'name' => 'WordPress Support Paket STANDARD', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 59.90, 'minimum_term_months' => 3],
            ['slug' => 'support-wp-premium', 'name' => 'WordPress Support Paket PREMIUM', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 119.90, 'minimum_term_months' => 6],
            ['slug' => 'support-ecommerce-plus', 'name' => 'Support Paket eCOMMERCE+', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 169.90, 'minimum_term_months' => 12],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function qrCode(): array
    {
        $c = ServiceCatalogCategory::QrCode;
        $u = ServiceCatalogUnit::Month;
        $b = ServiceCatalogBillingInterval::Monthly;

        return [
            ['slug' => 'qr-admin-5', 'name' => 'Verwaltung & Statistik für 5 QR-Codes', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 5.00],
            ['slug' => 'qr-admin-15', 'name' => 'Verwaltung & Statistik für 15 QR-Codes', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 10.00],
            ['slug' => 'qr-admin-25', 'name' => 'Verwaltung & Statistik für 25 QR-Codes', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 15.00],
            ['slug' => 'qr-admin-50', 'name' => 'Verwaltung & Statistik für 50 QR-Codes', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 25.00],
            ['slug' => 'qr-admin-500', 'name' => 'Verwaltung & Statistik für 500 QR-Codes', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 35.00],
            ['slug' => 'qr-tool-login-shortener', 'name' => 'QR Code Tool mit eigenem Login und Shortener', 'category' => $c, 'unit' => $u, 'billing_interval' => ServiceCatalogBillingInterval::FlatRate, 'sales_price' => 10.00],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function mailExchange(): array
    {
        $c = ServiceCatalogCategory::MailExchange;
        $u = ServiceCatalogUnit::Month;
        $b = ServiceCatalogBillingInterval::Monthly;

        return [
            ['slug' => 'mail-managedexchange-m', 'name' => 'ManagedExchange M ohne Outlook', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 9.99],
            ['slug' => 'mail-managedexchange-l-outlook', 'name' => 'ManagedExchange L mit Outlook', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 13.00],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function domains(): array
    {
        $c = ServiceCatalogCategory::Domain;
        $u = ServiceCatalogUnit::Year;
        $b = ServiceCatalogBillingInterval::Yearly;

        /** @var array<string, array{0: float, 1: string}> slug suffix => [price, display label without leading dot where redundant] */
        $map = [
            'at' => [26.00, '.at'],
            'me' => [29.00, '.me'],
            'jetzt' => [26.00, '.jetzt'],
            'it' => [25.00, '.it'],
            'social' => [39.00, '.social'],
            'world' => [55.00, '.world'],
            'group' => [79.00, '.group'],
            'shop' => [83.00, '.shop'],
            'store' => [89.00, '.store'],
            'biz' => [33.00, '.biz'],
            'cc' => [55.00, '.cc'],
            'bz' => [39.90, '.bz'],
            'ch' => [33.00, '.ch'],
            'com-net-org' => [24.00, '.com / .net / .org'],
            'de' => [22.00, '.de'],
            'eu' => [24.00, '.eu'],
            'info' => [28.00, '.info'],
            'li' => [36.00, '.li'],
            'tv' => [59.90, '.tv'],
            'software' => [52.90, '.software'],
            'events' => [43.00, '.events'],
            'asia' => [49.90, '.asia'],
            'tirol' => [76.30, '.tirol'],
            'cafe' => [39.00, '.cafe'],
            'ag' => [145.00, '.ag'],
            'blog' => [49.00, '.blog'],
            'cloud' => [79.00, '.cloud'],
            'io' => [99.00, '.io'],
            'yoga' => [47.00, '.yoga'],
            'ai' => [250.00, '.ai'],
            'rocks' => [16.00, '.rocks'],
            'team' => [59.90, '.team'],
        ];

        $rows = [];
        foreach ($map as $key => [$price, $label]) {
            $rows[] = [
                'slug' => 'domain-tld-'.$key,
                'name' => 'Domain – '.$label.' (Jahresgebühr)',
                'category' => $c,
                'unit' => $u,
                'billing_interval' => $b,
                'sales_price' => $price,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function additionalServices(): array
    {
        $c = ServiceCatalogCategory::AdditionalService;
        $piece = ServiceCatalogUnit::Piece;

        return [
            [
                'slug' => 'addon-external-domain-binding',
                'name' => 'Einbindung als externe Domain',
                'category' => $c,
                'unit' => $piece,
                'billing_interval' => ServiceCatalogBillingInterval::OneTime,
                'sales_price' => 15.00,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function storageAddons(): array
    {
        $c = ServiceCatalogCategory::Storage;
        $u = ServiceCatalogUnit::Month;
        $b = ServiceCatalogBillingInterval::Monthly;

        return [
            ['slug' => 'storage-webspace-5gb', 'name' => '5 GB zusätzlicher Webspace', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 10.00],
            ['slug' => 'storage-mailspace-5gb', 'name' => '5 GB zusätzlicher Mailspace', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 10.00],
            ['slug' => 'storage-webspace-10gb', 'name' => '10 GB zusätzlicher Webspace', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 20.00],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function issuu(): array
    {
        $c = ServiceCatalogCategory::ToolSaas;
        $u = ServiceCatalogUnit::Month;
        $b = ServiceCatalogBillingInterval::Monthly;

        return [
            ['slug' => 'issuu-10-pdfs', 'name' => 'ISSUU bis 10 PDFs', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 5.00],
            ['slug' => 'issuu-20-pdfs', 'name' => 'ISSUU bis 20 PDFs', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 10.00],
            ['slug' => 'issuu-50-pdfs', 'name' => 'ISSUU bis 50 PDFs', 'category' => $c, 'unit' => $u, 'billing_interval' => $b, 'sales_price' => 15.00],
        ];
    }
}
