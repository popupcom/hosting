<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('vendor');
            $table->string('license_type');
            $table->text('license_reference')->nullable();
            $table->unsignedInteger('max_installations')->nullable();
            $table->unsignedInteger('used_installations')->default(0);
            $table->date('expires_at')->nullable();
            $table->unsignedSmallInteger('cancellation_notice_days')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->string('billing_interval', 32)->nullable();
            $table->string('status', 32)->default('active');
            $table->date('reminder_at')->nullable();
            $table->string('lastpass_reference', 512)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('expires_at');
            $table->index('reminder_at');
            $table->index('vendor');
        });

        Schema::create('license_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['license_id', 'project_id']);
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_project');
        Schema::dropIfExists('licenses');
    }
};
