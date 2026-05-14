<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('domain_name')->unique();
            $table->string('registrar')->nullable();
            $table->string('hosting_provider')->nullable();
            $table->string('autodns_id', 64)->nullable();
            $table->text('dns_zone')->nullable();
            $table->text('nameservers')->nullable();
            $table->date('expires_at')->nullable();
            $table->unsignedSmallInteger('cancellation_notice_days')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->string('billing_interval', 32)->nullable();
            $table->string('status', 32)->default('active');
            $table->date('reminder_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('expires_at');
            $table->index('reminder_at');
            $table->index(['project_id', 'domain_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_domains');
    }
};
