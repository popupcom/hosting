<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_catalog_items')) {
            return;
        }

        Schema::table('service_catalog_items', function (Blueprint $table) {
            if (! Schema::hasColumn('service_catalog_items', 'slug')) {
                $table->string('slug', 128)->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('service_catalog_items', 'minimum_term_months')) {
                $table->unsignedSmallInteger('minimum_term_months')->nullable()->after('billing_interval');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_catalog_items')) {
            return;
        }

        Schema::table('service_catalog_items', function (Blueprint $table) {
            if (Schema::hasColumn('service_catalog_items', 'minimum_term_months')) {
                $table->dropColumn('minimum_term_months');
            }
            if (Schema::hasColumn('service_catalog_items', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
        });
    }
};
