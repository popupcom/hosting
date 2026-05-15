<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('project_services')) {
            return;
        }

        Schema::table('project_services', function (Blueprint $table) {
            if (! Schema::hasColumn('project_services', 'moco_invoice_reference')) {
                $table->string('moco_invoice_reference', 255)->nullable()->after('notes');
            }
            if (! Schema::hasColumn('project_services', 'price_change_effective_from')) {
                $table->date('price_change_effective_from')->nullable()->after('moco_invoice_reference');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('project_services')) {
            return;
        }

        Schema::table('project_services', function (Blueprint $table) {
            if (Schema::hasColumn('project_services', 'price_change_effective_from')) {
                $table->dropColumn('price_change_effective_from');
            }
            if (Schema::hasColumn('project_services', 'moco_invoice_reference')) {
                $table->dropColumn('moco_invoice_reference');
            }
        });
    }
};
