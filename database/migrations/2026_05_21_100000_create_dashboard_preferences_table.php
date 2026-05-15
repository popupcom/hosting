<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('visible_widget_keys')->nullable();
            $table->json('widget_order')->nullable();
            $table->json('filters')->nullable();
            $table->boolean('annualized_view')->default(false);
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::table('cost_line_items', function (Blueprint $table) {
            $table->index(['is_active', 'moco_sync_status', 'created_at'], 'cost_line_items_dashboard_idx');
        });
    }

    public function down(): void
    {
        Schema::table('cost_line_items', function (Blueprint $table) {
            $table->dropIndex('cost_line_items_dashboard_idx');
        });

        Schema::dropIfExists('dashboard_preferences');
    }
};
