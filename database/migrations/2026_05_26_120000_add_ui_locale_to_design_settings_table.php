<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('design_settings', function (Blueprint $table): void {
            $table->string('ui_locale', 8)->default('de')->after('singleton_key');
        });
    }

    public function down(): void
    {
        Schema::table('design_settings', function (Blueprint $table): void {
            $table->dropColumn('ui_locale');
        });
    }
};
