<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 128)->nullable();
            $table->string('license_model', 16)->default('shared');
            $table->string('billing_type', 16)->default('yearly');
            $table->decimal('default_cost_price', 10, 2)->nullable();
            $table->decimal('default_selling_price', 10, 2)->nullable();
            $table->unsignedInteger('installation_limit')->nullable();
            $table->boolean('centrally_managed')->default(true);
            $table->text('description')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index('status');
            $table->index('category');
        });

        Schema::create('project_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('license_product_id')->constrained()->restrictOnDelete();
            $table->text('license_code')->nullable();
            $table->date('expires_at')->nullable();
            $table->unsignedSmallInteger('cancellation_notice_days')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->string('status', 32)->default('active');
            $table->string('lastpass_reference', 512)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index('expires_at');
        });

        Schema::create('service_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 128)->nullable();
            $table->text('service_description')->nullable();
            $table->string('billing_type', 16)->default('monthly');
            $table->decimal('default_cost_price', 10, 2)->nullable();
            $table->decimal('default_selling_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('category');
        });

        Schema::create('project_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_product_id')->constrained()->restrictOnDelete();
            $table->date('started_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->unsignedSmallInteger('cancellation_notice_days')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->string('status', 32)->default('active');
            $table->string('external_reference', 512)->nullable();
            $table->string('lastpass_reference', 512)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index('expires_at');
        });

        $this->migrateLegacyLicenses();

        Schema::dropIfExists('license_project');
        Schema::dropIfExists('licenses');
    }

    private const LEGACY_LICENSE_CLASS = 'App\Models\License';

    private const PROJECT_LICENSE_CLASS = 'App\Models\ProjectLicense';

    public function down(): void
    {
        Schema::dropIfExists('project_services');
        Schema::dropIfExists('service_products');
        Schema::dropIfExists('project_licenses');
        Schema::dropIfExists('license_products');

        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('vendor');
            $table->string('license_type');
            $table->text('license_reference')->nullable();
            $table->unsignedInteger('max_installations')->nullable();
            $table->unsignedInteger('used_installations')->default(0);
            $table->date('expires_at')->nullable();
            $table->unsignedSmallInteger('cancellation_notice_days')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->string('billing_interval', 32)->nullable();
            $table->string('status', 32)->default('active');
            $table->date('reminder_at')->nullable();
            $table->string('lastpass_reference', 512)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('expires_at');
            $table->index('reminder_at');
            $table->index('vendor');
        });

        Schema::create('license_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['license_id', 'project_id']);
            $table->index('project_id');
        });
    }

    private function migrateLegacyLicenses(): void
    {
        if (! Schema::hasTable('licenses')) {
            return;
        }

        $licenseClass = self::LEGACY_LICENSE_CLASS;
        $legacyLicenses = DB::table('licenses')->orderBy('id')->get();

        /** @var array<int, int> $oldLicenseIdToProductId */
        $oldLicenseIdToProductId = [];

        foreach ($legacyLicenses as $row) {
            $billingType = $this->mapBillingIntervalToCadence($row->billing_interval ?? null);

            $descriptionParts = array_filter([
                $row->notes ? (string) $row->notes : null,
                isset($row->vendor) && $row->vendor !== '' ? 'Anbieter: '.$row->vendor : null,
                isset($row->used_installations) && (int) $row->used_installations > 0
                    ? 'Genutzte Installationen (Altbestand): '.$row->used_installations
                    : null,
            ], static fn (?string $p): bool => $p !== null && $p !== '');

            $description = $descriptionParts !== [] ? implode("\n\n", $descriptionParts) : null;

            $productId = DB::table('license_products')->insertGetId([
                'name' => $row->name,
                'category' => $row->license_type,
                'license_model' => 'shared',
                'billing_type' => $billingType,
                'default_cost_price' => $row->cost_price,
                'default_selling_price' => $row->selling_price,
                'installation_limit' => $row->max_installations,
                'centrally_managed' => true,
                'description' => $description,
                'status' => 'active',
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ]);

            $oldLicenseIdToProductId[(int) $row->id] = $productId;
        }

        /** @var array<string, int> $pairKeyToProjectLicenseId */
        $pairKeyToProjectLicenseId = [];

        $pivots = DB::table('license_project')->orderBy('id')->get();

        foreach ($pivots as $pivot) {
            $licenseId = (int) $pivot->license_id;
            $legacy = $legacyLicenses->firstWhere('id', $licenseId);

            if ($legacy === null || ! isset($oldLicenseIdToProductId[$licenseId])) {
                continue;
            }

            $productId = $oldLicenseIdToProductId[$licenseId];

            $plId = DB::table('project_licenses')->insertGetId([
                'project_id' => $pivot->project_id,
                'license_product_id' => $productId,
                'license_code' => $legacy->license_reference,
                'expires_at' => $legacy->expires_at,
                'cancellation_notice_days' => $legacy->cancellation_notice_days,
                'cost_price' => $legacy->cost_price,
                'selling_price' => $legacy->selling_price,
                'status' => $legacy->status,
                'lastpass_reference' => $legacy->lastpass_reference,
                'notes' => null,
                'created_at' => $pivot->created_at ?? now(),
                'updated_at' => $pivot->updated_at ?? now(),
            ]);

            $pairKeyToProjectLicenseId[$licenseId.'|'.$pivot->project_id] = $plId;
        }

        $this->rewireMorphs($licenseClass, $oldLicenseIdToProductId, $pairKeyToProjectLicenseId);
    }

    /**
     * @param  array<int, int>  $oldLicenseIdToProductId
     * @param  array<string, int>  $pairKeyToProjectLicenseId
     */
    private function rewireMorphs(string $licenseClass, array $oldLicenseIdToProductId, array $pairKeyToProjectLicenseId): void
    {
        $licenseTypeVariants = array_unique(array_filter([
            $licenseClass,
            Str::start($licenseClass, '\\'),
            ltrim($licenseClass, '\\'),
        ]));

        $costRows = DB::table('cost_line_items')
            ->whereIn('billable_type', $licenseTypeVariants)
            ->whereNotNull('billable_id')
            ->get();

        foreach ($costRows as $costRow) {
            $oldLicenseId = (int) $costRow->billable_id;
            $projectId = (int) $costRow->project_id;

            if (! isset($oldLicenseIdToProductId[$oldLicenseId])) {
                continue;
            }

            $key = $oldLicenseId.'|'.$projectId;
            $projectLicenseId = $pairKeyToProjectLicenseId[$key] ?? null;

            if ($projectLicenseId === null) {
                $productId = $oldLicenseIdToProductId[$oldLicenseId];
                $projectLicenseId = DB::table('project_licenses')->insertGetId([
                    'project_id' => $projectId,
                    'license_product_id' => $productId,
                    'license_code' => null,
                    'expires_at' => null,
                    'cancellation_notice_days' => null,
                    'cost_price' => null,
                    'selling_price' => null,
                    'status' => 'active',
                    'lastpass_reference' => null,
                    'notes' => 'Automatisch angelegt bei Migration (Kostenposition ohne Pivot-Eintrag).',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $pairKeyToProjectLicenseId[$key] = $projectLicenseId;
            }

            DB::table('cost_line_items')->where('id', $costRow->id)->update([
                'billable_type' => self::PROJECT_LICENSE_CLASS,
                'billable_id' => $projectLicenseId,
            ]);
        }

        $reminderRows = DB::table('reminders')
            ->whereIn('remindable_type', $licenseTypeVariants)
            ->whereNotNull('remindable_id')
            ->get();

        foreach ($reminderRows as $reminder) {
            $oldLicenseId = (int) $reminder->remindable_id;

            if (! isset($oldLicenseIdToProductId[$oldLicenseId])) {
                DB::table('reminders')->where('id', $reminder->id)->delete();

                continue;
            }

            $productId = $oldLicenseIdToProductId[$oldLicenseId];

            $projectLicenseId = DB::table('project_licenses')
                ->where('license_product_id', $productId)
                ->orderBy('id')
                ->value('id');

            if ($projectLicenseId === null) {
                DB::table('reminders')->where('id', $reminder->id)->delete();

                continue;
            }

            DB::table('reminders')->where('id', $reminder->id)->update([
                'remindable_type' => self::PROJECT_LICENSE_CLASS,
                'remindable_id' => $projectLicenseId,
            ]);
        }
    }

    private function mapBillingIntervalToCadence(?string $interval): string
    {
        if ($interval === null || $interval === '') {
            return 'yearly';
        }

        $i = Str::lower($interval);

        if (Str::contains($i, ['month', 'monat'])) {
            return 'monthly';
        }

        if (Str::contains($i, ['year', 'annual', 'jahr'])) {
            return 'yearly';
        }

        if (Str::contains($i, ['once', 'one', 'einmal'])) {
            return 'one_time';
        }

        return 'yearly';
    }
};
