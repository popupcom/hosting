<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            $table->decimal('quantity', 12, 4)->default(1)->after('service_catalog_item_id');
            $table->string('billing_interval', 32)->nullable()->after('selling_price');
            $table->string('moco_sync_status', 32)->default('pending')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'billing_interval', 'moco_sync_status']);
        });
    }
};
