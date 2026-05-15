<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_catalog_items')) {
            DB::table('service_catalog_items')->where('unit', 'flat')->update(['unit' => 'flat_rate']);
            DB::table('service_catalog_items')->where('billing_interval', 'flat')->update(['billing_interval' => 'flat_rate']);
        }

        if (Schema::hasTable('project_services')) {
            DB::table('project_services')->where('status', 'suspended')->update(['status' => 'paused']);
            DB::table('project_services')->where('moco_sync_status', 'pending')->update(['moco_sync_status' => 'not_synced']);
            DB::table('project_services')->where('moco_sync_status', 'failed')->update(['moco_sync_status' => 'error']);
            DB::table('project_services')->where('moco_sync_status', 'skipped')->update(['moco_sync_status' => 'not_synced']);
            if (Schema::hasColumn('project_services', 'billing_interval')) {
                DB::table('project_services')->where('billing_interval', 'flat')->update(['billing_interval' => 'flat_rate']);
            }
        }

        if (Schema::hasTable('service_catalog_items')) {
            Schema::table('service_catalog_items', function (Blueprint $table) {
                $table->renameColumn('standard_quantity', 'default_quantity');
                $table->renameColumn('selling_price', 'sales_price');
            });
        }

        if (Schema::hasTable('project_services')) {
            Schema::table('project_services', function (Blueprint $table) {
                $table->renameColumn('started_at', 'start_date');
                $table->renameColumn('expires_at', 'end_date');
                $table->renameColumn('cost_price', 'custom_cost_price');
                $table->renameColumn('selling_price', 'custom_sales_price');
                $table->renameColumn('billing_interval', 'custom_billing_interval');
            });

            $toDrop = array_values(array_filter([
                Schema::hasColumn('project_services', 'cancellation_notice_days') ? 'cancellation_notice_days' : null,
                Schema::hasColumn('project_services', 'external_reference') ? 'external_reference' : null,
                Schema::hasColumn('project_services', 'lastpass_reference') ? 'lastpass_reference' : null,
            ]));
            if ($toDrop !== []) {
                Schema::table('project_services', function (Blueprint $table) use ($toDrop) {
                    $table->dropColumn($toDrop);
                });
            }
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Migration 2026_05_24_120000 kann nicht automatisch zurückgerollt werden.');
    }
};
