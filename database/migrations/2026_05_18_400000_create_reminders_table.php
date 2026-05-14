<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->morphs('remindable');
            $table->date('reminder_at');
            $table->string('status', 32)->default('pending');
            $table->text('message')->nullable();
            $table->boolean('is_done')->default(false);
            $table->timestamps();

            $table->index(['reminder_at', 'status']);
            $table->index('is_done');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
