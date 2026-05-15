<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyRows = $this->captureLegacyAssignments();
        $this->rebuildSupportPackagesTable();
        $this->createProjectSupportPackagesTable();
        $this->migrateLegacyAssignments($legacyRows);
    }

    public function down(): void
    {
        throw new RuntimeException('Migration 2026_06_01_100000 kann nicht automatisch zurückgerollt werden.');
    }

    /**
     * @return list<object>
     */
    private function captureLegacyAssignments(): array
    {
        if (! Schema::hasTable('support_packages') || ! Schema::hasColumn('support_packages', 'project_id')) {
            return [];
        }

        return DB::table('support_packages')
            ->whereNotNull('project_id')
            ->orderBy('id')
            ->get()
            ->all();
    }

    private function rebuildSupportPackagesTable(): void
    {
        Schema::dropIfExists('support_packages');

        Schema::create('support_packages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignId('service_catalog_item_id')->constrained('service_catalog_items')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->text('included_services')->nullable();
            $table->decimal('monthly_minutes', 10, 2)->nullable();
            $table->decimal('yearly_hours', 10, 2)->nullable();
            $table->string('update_frequency')->nullable();
            $table->string('response_time')->nullable();
            $table->unsignedSmallInteger('minimum_term_months')->default(6);
            $table->string('billing_interval', 32)->default('yearly');
            $table->boolean('bill_yearly_in_advance')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('includes_daily_backups')->default(false);
            $table->boolean('includes_plugin_updates')->default(false);
            $table->boolean('includes_link_monitoring')->default(false);
            $table->boolean('includes_security_checks')->default(false);
            $table->boolean('includes_uptime_monitoring')->default(false);
            $table->boolean('includes_wordpress_core_update')->default(false);
            $table->boolean('includes_theme_update')->default(false);
            $table->boolean('includes_performance_check')->default(false);
            $table->boolean('includes_multisite')->default(false);
            $table->boolean('includes_custom_websites')->default(false);
            $table->boolean('includes_online_shops')->default(false);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    private function createProjectSupportPackagesTable(): void
    {
        if (Schema::hasTable('project_support_packages')) {
            return;
        }

        Schema::create('project_support_packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('support_package_id')->constrained()->restrictOnDelete();
            $table->foreignId('project_service_id')->nullable()->constrained('project_services')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('cancellation_date')->nullable();
            $table->boolean('do_not_renew')->default(false);
            $table->string('status', 32)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index('support_package_id');
        });
    }

    /**
     * @param  list<object>  $legacyRows
     */
    private function migrateLegacyAssignments(array $legacyRows): void
    {
        if ($legacyRows === []) {
            return;
        }

        foreach ($legacyRows as $row) {
            $packageId = DB::table('support_packages')
                ->where('name', $row->name)
                ->value('id');

            if ($packageId === null) {
                continue;
            }

            DB::table('project_support_packages')->insert([
                'project_id' => $row->project_id,
                'support_package_id' => $packageId,
                'start_date' => $row->starts_at ?? now()->toDateString(),
                'status' => in_array($row->status, ['active', 'paused'], true) ? 'active' : ($row->status ?? 'active'),
                'notes' => $row->notes,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ]);
        }
    }
};
