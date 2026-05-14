<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_sync_states', function (Blueprint $table) {
            $table->id();
            $table->morphs('syncable');
            $table->string('provider', 32);
            $table->string('status', 32)->default('pending');
            $table->string('external_id', 128)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['syncable_type', 'syncable_id', 'provider']);
            $table->index(['provider', 'external_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_sync_states');
    }
};
