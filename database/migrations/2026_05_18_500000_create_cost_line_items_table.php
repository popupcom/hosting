<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('line_type', 32);
            $table->nullableMorphs('billable');
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->string('billing_interval', 32)->nullable();
            $table->string('moco_sync_status', 32)->default('pending');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'project_id']);
            $table->index('line_type');
            $table->index('moco_sync_status');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_line_items');
    }
};
