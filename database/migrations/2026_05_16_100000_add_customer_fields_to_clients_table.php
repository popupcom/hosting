<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('company')->default('');
            $table->string('email')->nullable();
            $table->string('phone', 48)->nullable();
            $table->text('address')->nullable();
            $table->string('moco_customer_id', 64)->nullable();
            $table->string('status', 32)->default('active');

            $table->unique('email');
            $table->unique('moco_customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropUnique(['moco_customer_id']);
            $table->dropColumn([
                'company',
                'email',
                'phone',
                'address',
                'moco_customer_id',
                'status',
            ]);
        });
    }
};
