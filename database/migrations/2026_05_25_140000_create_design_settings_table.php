<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_settings', function (Blueprint $table) {
            $table->id();
            $table->string('singleton_key')->default('app');
            $table->unique('singleton_key');
            $table->string('app_name')->nullable();
            $table->string('primary_color', 32)->nullable();
            $table->string('secondary_color', 32)->nullable();
            $table->string('accent_color', 32)->nullable();
            $table->string('background_color', 32)->nullable();
            $table->string('text_color', 32)->nullable();
            $table->string('border_radius', 64)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->longText('custom_css')->nullable();
            $table->longText('design_notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_settings');
    }
};
