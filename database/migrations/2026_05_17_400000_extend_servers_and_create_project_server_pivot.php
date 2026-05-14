<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->string('operating_system')->nullable();
            $table->json('php_versions')->nullable();
            $table->date('contract_expires_at')->nullable();
            $table->unsignedSmallInteger('cancellation_notice_days')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->string('billing_interval', 32)->nullable();
            $table->string('lastpass_reference', 512)->nullable();
        });

        Schema::create('project_server', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(true);
            $table->timestamps();

            $table->unique(['project_id', 'server_id']);
            $table->index('server_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_server');

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn([
                'operating_system',
                'php_versions',
                'contract_expires_at',
                'cancellation_notice_days',
                'cost_price',
                'selling_price',
                'billing_interval',
                'lastpass_reference',
            ]);
        });
    }
};
