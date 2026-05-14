<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('scope_of_services')->nullable();
            $table->string('response_time')->nullable();
            $table->string('update_interval')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('billing_interval', 32)->nullable();
            $table->date('starts_at')->nullable();
            $table->unsignedSmallInteger('cancellation_notice_days')->nullable();
            $table->string('status', 32)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_packages');
    }
};
