<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->refactorLicenseProducts();

        if (Schema::hasTable('project_licenses') && ! Schema::hasTable('project_license_assignments')) {
            Schema::rename('project_licenses', 'project_license_assignments');
        }

        $this->refactorProjectLicenseAssignments();
    }

    public function down(): void
    {
        throw new RuntimeException('Migration 2026_05_29_100000 kann nicht automatisch zurückgerollt werden.');
    }

    private function refactorLicenseProducts(): void
    {
        if (! Schema::hasTable('license_products')) {
            return;
        }

        if (! Schema::hasColumn('license_products', 'billing_type') && Schema::hasColumn('license_products', 'total_available_licenses')) {
            return;
        }

        Schema::table('license_products', function (Blueprint $table): void {
            if (! Schema::hasColumn('license_products', 'provider')) {
                $table->string('provider')->nullable()->after('name');
            }
            if (! Schema::hasColumn('license_products', 'total_available_licenses')) {
                $table->unsignedInteger('total_available_licenses')->default(0)->after('category');
            }
            if (! Schema::hasColumn('license_products', 'notes') && Schema::hasColumn('license_products', 'description')) {
                $table->renameColumn('description', 'notes');
            } elseif (! Schema::hasColumn('license_products', 'notes')) {
                $table->text('notes')->nullable();
            }
        });

        if (Schema::hasColumn('license_products', 'installation_limit')) {
            DB::table('license_products')
                ->whereNull('total_available_licenses')
                ->orWhere('total_available_licenses', 0)
                ->update([
                    'total_available_licenses' => DB::raw('COALESCE(installation_limit, 0)'),
                ]);
        }

        Schema::table('license_products', function (Blueprint $table): void {
            foreach ([
                'billing_type',
                'default_cost_price',
                'default_selling_price',
                'installation_limit',
                'centrally_managed',
            ] as $col) {
                if (Schema::hasColumn('license_products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function refactorProjectLicenseAssignments(): void
    {
        if (! Schema::hasTable('project_license_assignments')) {
            return;
        }

        Schema::table('project_license_assignments', function (Blueprint $table): void {
            if (! Schema::hasColumn('project_license_assignments', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('project_license_assignments', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('license_product_id');
            }
            if (! Schema::hasColumn('project_license_assignments', 'activated_at')) {
                $table->timestamp('activated_at')->nullable()->after('assigned_at');
            }
            if (! Schema::hasColumn('project_license_assignments', 'cancellation_effective_date')) {
                $table->date('cancellation_effective_date')->nullable()->after('cancelled_at');
            }
            if (! Schema::hasColumn('project_license_assignments', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable();
            }
            if (! Schema::hasColumn('project_license_assignments', 'do_not_renew')) {
                $table->boolean('do_not_renew')->default(false);
            }
        });

        DB::table('project_license_assignments')->whereNull('assigned_at')->update([
            'assigned_at' => DB::raw('created_at'),
        ]);
        DB::table('project_license_assignments')->whereNull('activated_at')->update([
            'activated_at' => DB::raw('COALESCE(start_date, assigned_at, created_at)'),
        ]);

        if (Schema::hasColumn('project_license_assignments', 'expires_at')) {
            DB::table('project_license_assignments')
                ->whereNull('cancellation_effective_date')
                ->whereNotNull('expires_at')
                ->update(['cancellation_effective_date' => DB::raw('expires_at')]);
        }
        if (Schema::hasColumn('project_license_assignments', 'end_date')) {
            DB::table('project_license_assignments')
                ->whereNull('cancellation_effective_date')
                ->whereNotNull('end_date')
                ->update(['cancellation_effective_date' => DB::raw('end_date')]);
        }

        DB::table('project_license_assignments')->where('status', 'suspended')->update(['status' => 'cancelled']);

        if (Schema::hasColumn('project_license_assignments', 'do_not_renew')) {
            DB::table('project_license_assignments')
                ->where('do_not_renew', true)
                ->where('status', 'active')
                ->update(['status' => 'pending_cancellation']);
        }

        $projectClientIds = DB::table('projects')->pluck('client_id', 'id');
        foreach (DB::table('project_license_assignments')->whereNull('client_id')->orderBy('id')->lazyById() as $row) {
            $clientId = $projectClientIds[$row->project_id] ?? null;
            if ($clientId !== null) {
                DB::table('project_license_assignments')
                    ->where('id', $row->id)
                    ->update(['client_id' => $clientId]);
            }
        }

        $this->dropLegacyAssignmentIndexes();
        $this->dropLegacyAssignmentForeignKeys();

        Schema::table('project_license_assignments', function (Blueprint $table): void {
            $drop = array_filter([
                Schema::hasColumn('project_license_assignments', 'license_code') ? 'license_code' : null,
                Schema::hasColumn('project_license_assignments', 'cost_price') ? 'cost_price' : null,
                Schema::hasColumn('project_license_assignments', 'selling_price') ? 'selling_price' : null,
                Schema::hasColumn('project_license_assignments', 'lastpass_reference') ? 'lastpass_reference' : null,
                Schema::hasColumn('project_license_assignments', 'billing_group_id') ? 'billing_group_id' : null,
                Schema::hasColumn('project_license_assignments', 'moco_sync_status') ? 'moco_sync_status' : null,
                Schema::hasColumn('project_license_assignments', 'cancellation_notice_days') ? 'cancellation_notice_days' : null,
                Schema::hasColumn('project_license_assignments', 'next_renewal_date') ? 'next_renewal_date' : null,
                Schema::hasColumn('project_license_assignments', 'cancellation_notice_until') ? 'cancellation_notice_until' : null,
                Schema::hasColumn('project_license_assignments', 'cancellation_date') ? 'cancellation_date' : null,
                Schema::hasColumn('project_license_assignments', 'renews_automatically') ? 'renews_automatically' : null,
                Schema::hasColumn('project_license_assignments', 'start_date') ? 'start_date' : null,
                Schema::hasColumn('project_license_assignments', 'end_date') ? 'end_date' : null,
                Schema::hasColumn('project_license_assignments', 'expires_at') ? 'expires_at' : null,
                Schema::hasColumn('project_license_assignments', 'created_by') ? 'created_by' : null,
                Schema::hasColumn('project_license_assignments', 'updated_by') ? 'updated_by' : null,
            ]);
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });

        if (Schema::hasTable('billing_group_items')) {
            DB::table('billing_group_items')
                ->where('billable_type', 'App\\Models\\ProjectLicense')
                ->delete();
        }
    }

    private function dropLegacyAssignmentIndexes(): void
    {
        if (! Schema::hasTable('project_license_assignments')) {
            return;
        }

        $connection = Schema::getConnection();

        foreach ([
            'project_licenses_moco_sync_status_index',
            'project_license_assignments_moco_sync_status_index',
            'project_licenses_next_renewal_date_index',
            'project_licenses_cancellation_date_index',
            'project_licenses_do_not_renew_index',
            'project_licenses_expires_at_index',
            'project_license_assignments_expires_at_index',
        ] as $indexName) {
            $connection->statement('DROP INDEX IF EXISTS '.$indexName);
        }

        foreach (['moco_sync_status', 'next_renewal_date', 'cancellation_date', 'do_not_renew', 'expires_at'] as $column) {
            if (! Schema::hasColumn('project_license_assignments', $column)) {
                continue;
            }

            try {
                Schema::table('project_license_assignments', function (Blueprint $table) use ($column): void {
                    $table->dropIndex([$column]);
                });
            } catch (Throwable) {
                // SQLite kann nach Tabellenumbenennung abweichende Indexnamen haben.
            }
        }
    }

    private function dropLegacyAssignmentForeignKeys(): void
    {
        if (! Schema::hasTable('project_license_assignments')) {
            return;
        }

        Schema::table('project_license_assignments', function (Blueprint $table): void {
            foreach (['billing_group_id', 'created_by', 'updated_by'] as $column) {
                if (! Schema::hasColumn('project_license_assignments', $column)) {
                    continue;
                }

                try {
                    $table->dropForeign([$column]);
                } catch (Throwable) {
                    // Bereits entfernt oder nie angelegt.
                }
            }
        });
    }
};
