<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reminders')) {
            return;
        }

        Schema::table('reminders', function (Blueprint $table): void {
            if (! Schema::hasColumn('reminders', 'assigned_user_id')) {
                $table->foreignId('assigned_user_id')
                    ->nullable()
                    ->after('remindable_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        if (! $this->indexExists('reminders', 'reminders_assigned_user_id_is_done_index')) {
            Schema::table('reminders', function (Blueprint $table): void {
                $table->index(['assigned_user_id', 'is_done']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('reminders') || ! Schema::hasColumn('reminders', 'assigned_user_id')) {
            return;
        }

        Schema::table('reminders', function (Blueprint $table): void {
            try {
                $table->dropForeign(['assigned_user_id']);
            } catch (Throwable) {
                // Bereits entfernt.
            }

            $table->dropColumn('assigned_user_id');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = Schema::getConnection()
            ->getSchemaBuilder()
            ->getIndexes($table);

        foreach ($indexes as $index) {
            if (($index['name'] ?? '') === $indexName) {
                return true;
            }
        }

        return false;
    }
};
