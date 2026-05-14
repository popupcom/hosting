<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hosting_providers', function (Blueprint $table) {
            $table->boolean('has_api')->default(false);
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('hosting_providers', function (Blueprint $table) {
            $table->dropColumn(['has_api', 'notes']);
        });
    }
};
