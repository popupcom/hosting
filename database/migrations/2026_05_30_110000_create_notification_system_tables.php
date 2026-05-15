<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('name');
        });

        Schema::create('notification_group_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notification_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['notification_group_id', 'user_id']);
        });

        Schema::create('notification_event_types', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category', 32);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });

        Schema::create('notification_group_event_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notification_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notification_event_type_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('send_email')->default(true);
            $table->boolean('send_in_app')->default(true);
            $table->timestamps();

            $table->unique(['notification_group_id', 'notification_event_type_id'], 'group_event_unique');
        });

        Schema::create('user_notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notification_event_type_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'notification_event_type_id'], 'user_event_pref_unique');
        });

        Schema::create('change_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('changeable_type');
            $table->unsignedBigInteger('changeable_id');
            $table->string('event_type', 64);
            $table->string('field_name', 128)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['changeable_type', 'changeable_id']);
            $table->index('event_type');
            $table->index('created_at');
        });

        Schema::create('notification_dispatches', function (Blueprint $table): void {
            $table->id();
            $table->string('dedupe_key', 128)->unique();
            $table->foreignId('notification_event_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('change_log_id')->nullable()->constrained('change_logs')->nullOnDelete();
            $table->boolean('sent_email')->default(false);
            $table->boolean('sent_in_app')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('notification_dispatches');
        Schema::dropIfExists('change_logs');
        Schema::dropIfExists('user_notification_preferences');
        Schema::dropIfExists('notification_group_event_settings');
        Schema::dropIfExists('notification_event_types');
        Schema::dropIfExists('notification_group_user');
        Schema::dropIfExists('notification_groups');
    }
};
