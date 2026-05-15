<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('license_products')) {
            Schema::table('license_products', function (Blueprint $table): void {
                if (! Schema::hasColumn('license_products', 'shared_license_code')) {
                    $table->text('shared_license_code')->nullable()->after('license_model');
                }
                if (! Schema::hasColumn('license_products', 'requires_individual_license_code')) {
                    $table->boolean('requires_individual_license_code')->default(false)->after('shared_license_code');
                }
            });
        }

        if (Schema::hasTable('project_license_assignments')) {
            Schema::table('project_license_assignments', function (Blueprint $table): void {
                if (! Schema::hasColumn('project_license_assignments', 'license_code')) {
                    $table->text('license_code')->nullable()->after('license_product_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('license_products')) {
            Schema::table('license_products', function (Blueprint $table): void {
                foreach (['shared_license_code', 'requires_individual_license_code'] as $column) {
                    if (Schema::hasColumn('license_products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('project_license_assignments')) {
            Schema::table('project_license_assignments', function (Blueprint $table): void {
                if (Schema::hasColumn('project_license_assignments', 'license_code')) {
                    $table->dropColumn('license_code');
                }
            });
        }
    }
};
