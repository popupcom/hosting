<?php

namespace Database\Seeders;

use App\Enums\ServiceCatalogBillingInterval;
use App\Models\ServiceCatalogItem;
use App\Models\SupportPackage;
use Illuminate\Database\Seeder;

class SupportPackageSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'support-wp-lite' => [
                'name' => 'LITE',
                'sort_order' => 10,
                'minimum_term_months' => 6,
                'includes_daily_backups' => true,
                'includes_plugin_updates' => true,
                'includes_link_monitoring' => true,
                'includes_security_checks' => true,
                'includes_uptime_monitoring' => true,
                'includes_wordpress_core_update' => true,
            ],
            'support-wp-standard' => [
                'name' => 'STANDARD',
                'sort_order' => 20,
                'minimum_term_months' => 6,
                'includes_daily_backups' => true,
                'includes_plugin_updates' => true,
                'includes_link_monitoring' => true,
                'includes_security_checks' => true,
                'includes_uptime_monitoring' => true,
                'includes_wordpress_core_update' => true,
                'includes_theme_update' => true,
            ],
            'support-wp-premium' => [
                'name' => 'PREMIUM',
                'sort_order' => 30,
                'minimum_term_months' => 6,
                'includes_daily_backups' => true,
                'includes_plugin_updates' => true,
                'includes_link_monitoring' => true,
                'includes_security_checks' => true,
                'includes_uptime_monitoring' => true,
                'includes_wordpress_core_update' => true,
                'includes_theme_update' => true,
                'includes_performance_check' => true,
                'includes_multisite' => true,
            ],
            'support-ecommerce-plus' => [
                'name' => 'eCOMMERCE+',
                'sort_order' => 40,
                'minimum_term_months' => 6,
                'includes_daily_backups' => true,
                'includes_plugin_updates' => true,
                'includes_link_monitoring' => true,
                'includes_security_checks' => true,
                'includes_uptime_monitoring' => true,
                'includes_wordpress_core_update' => true,
                'includes_theme_update' => true,
                'includes_performance_check' => true,
                'includes_multisite' => true,
                'includes_custom_websites' => true,
                'includes_online_shops' => true,
            ],
        ];

        foreach ($definitions as $slug => $attributes) {
            $catalogItem = ServiceCatalogItem::query()->where('slug', $slug)->first();
            if ($catalogItem === null) {
                continue;
            }

            SupportPackage::query()->updateOrCreate(
                ['name' => $attributes['name']],
                [
                    ...$attributes,
                    'service_catalog_item_id' => $catalogItem->id,
                    'description' => $catalogItem->description,
                    'billing_interval' => ServiceCatalogBillingInterval::Yearly,
                    'bill_yearly_in_advance' => true,
                    'is_active' => true,
                    'update_frequency' => 'Jährlich',
                    'response_time' => 'Werktags',
                ],
            );
        }
    }
}
