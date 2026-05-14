<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('maintenance_type', 64);
            $table->string('performed_by');
            $table->date('performed_on');
            $table->text('result');
            $table->boolean('has_errors')->default(false);
            $table->text('notes')->nullable();
            $table->string('managewp_reference', 255)->nullable();
            $table->timestamps();

            $table->index(['project_id', 'performed_on']);
            $table->index('maintenance_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_histories');
    }
};
