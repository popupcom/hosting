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
            if (! Schema::hasColumn('design_settings', 'notification_style')) {
                $table->json('notification_style')->nullable()->after('border_radius');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('design_settings', 'notification_style')) {
            return;
        }

        Schema::table('design_settings', function (Blueprint $table): void {
            $table->dropColumn('notification_style');
        });
    }
};
