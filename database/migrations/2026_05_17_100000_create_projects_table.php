<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('url', 512);
            $table->string('wordpress_version', 32)->nullable();
            $table->string('php_version', 32)->nullable();
            $table->string('managewp_site_id', 64)->nullable();
            $table->string('lastpass_reference', 512)->nullable();
            $table->string('moco_project_id', 64)->nullable();
            $table->string('status', 32)->default('active');
            $table->boolean('maintenance_contract')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('managewp_site_id');
            $table->unique('moco_project_id');
            $table->index('status');
            $table->index(['client_id', 'name']);
            $table->index('url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
