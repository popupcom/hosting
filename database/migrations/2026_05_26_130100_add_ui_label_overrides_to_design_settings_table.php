<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('design_settings')) {
            return;
        }

        Schema::table('design_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('design_settings', 'ui_label_overrides')) {
                $table->json('ui_label_overrides')->nullable()->after('ui_locale');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('design_settings', 'ui_label_overrides')) {
            return;
        }

        Schema::table('design_settings', function (Blueprint $table): void {
            $table->dropColumn('ui_label_overrides');
        });
    }
};
