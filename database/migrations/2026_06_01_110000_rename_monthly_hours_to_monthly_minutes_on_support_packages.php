<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('support_packages')) {
            return;
        }

        if (Schema::hasColumn('support_packages', 'monthly_hours') && ! Schema::hasColumn('support_packages', 'monthly_minutes')) {
            Schema::table('support_packages', function (Blueprint $table): void {
                $table->decimal('monthly_minutes', 10, 2)->nullable()->after('included_services');
            });

            DB::table('support_packages')
                ->whereNotNull('monthly_hours')
                ->update([
                    'monthly_minutes' => DB::raw('monthly_hours * 60'),
                ]);

            Schema::table('support_packages', function (Blueprint $table): void {
                $table->dropColumn('monthly_hours');
            });
        }

        if (! Schema::hasColumn('support_packages', 'monthly_minutes')) {
            Schema::table('support_packages', function (Blueprint $table): void {
                $table->decimal('monthly_minutes', 10, 2)->nullable()->after('included_services');
            });
        }

        DB::table('support_packages')
            ->whereNotNull('monthly_minutes')
            ->orderBy('id')
            ->each(function (object $row): void {
                $yearlyHours = round(((float) $row->monthly_minutes * 12) / 60, 2);
                DB::table('support_packages')
                    ->where('id', $row->id)
                    ->update(['yearly_hours' => $yearlyHours]);
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('support_packages') || ! Schema::hasColumn('support_packages', 'monthly_minutes')) {
            return;
        }

        Schema::table('support_packages', function (Blueprint $table): void {
            $table->decimal('monthly_hours', 8, 2)->nullable()->after('included_services');
        });

        DB::table('support_packages')
            ->whereNotNull('monthly_minutes')
            ->update([
                'monthly_hours' => DB::raw('monthly_minutes / 60'),
            ]);

        Schema::table('support_packages', function (Blueprint $table): void {
            $table->dropColumn('monthly_minutes');
        });
    }
};
