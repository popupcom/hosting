<?php

use App\Enums\BillingCadence;
use App\Enums\ServiceCatalogBillingInterval;
use App\Enums\ServiceCatalogCategory;
use App\Enums\ServiceCatalogUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_catalog_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 32);
            $table->text('description')->nullable();
            $table->string('unit', 32);
            $table->decimal('standard_quantity', 12, 4)->default(1);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->string('billing_interval', 32);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('moco_article_id', 64)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('category');
        });

        if (Schema::hasTable('service_products')) {
            foreach (DB::table('service_products')->orderBy('id')->cursor() as $row) {
                $billingCadence = BillingCadence::tryFrom((string) ($row->billing_type ?? BillingCadence::Monthly->value))
                    ?? BillingCadence::Monthly;
                $billingInterval = match ($billingCadence) {
                    BillingCadence::Monthly => ServiceCatalogBillingInterval::Monthly,
                    BillingCadence::Yearly => ServiceCatalogBillingInterval::Yearly,
                    BillingCadence::OneTime => ServiceCatalogBillingInterval::OneTime,
                };

                DB::table('service_catalog_items')->insert([
                    'id' => $row->id,
                    'name' => $row->name,
                    'category' => $this->mapLegacyCategory((string) ($row->category ?? ''))->value,
                    'description' => $row->service_description,
                    'unit' => ServiceCatalogUnit::Piece->value,
                    'standard_quantity' => 1,
                    'cost_price' => $row->default_cost_price,
                    'selling_price' => $row->default_selling_price,
                    'billing_interval' => $billingInterval->value,
                    'is_active' => (bool) ($row->is_active ?? true),
                    'sort_order' => (int) $row->id,
                    'moco_article_id' => null,
                    'notes' => null,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        if (Schema::hasTable('project_services') && Schema::hasColumn('project_services', 'service_product_id')) {
            Schema::table('project_services', function (Blueprint $table) {
                $table->dropForeign(['service_product_id']);
            });

            Schema::table('project_services', function (Blueprint $table) {
                $table->renameColumn('service_product_id', 'service_catalog_item_id');
            });

            Schema::table('project_services', function (Blueprint $table) {
                $table->foreign('service_catalog_item_id')
                    ->references('id')
                    ->on('service_catalog_items')
                    ->restrictOnDelete();
            });
        }

        Schema::dropIfExists('service_products');
    }

    public function down(): void
    {
        throw new RuntimeException('Migration 2026_05_22_120000 kann nicht automatisch zurückgerollt werden (Datenübernahme service_products → service_catalog_items).');
    }

    private function mapLegacyCategory(string $raw): ServiceCatalogCategory
    {
        $s = Str::lower(trim($raw));

        return match (true) {
            Str::contains($s, ['hosting']) => ServiceCatalogCategory::Hosting,
            Str::contains($s, ['domain']) => ServiceCatalogCategory::Domain,
            Str::contains($s, ['ssl', 'tls']) => ServiceCatalogCategory::Ssl,
            Str::contains($s, ['lizenz', 'license']) => ServiceCatalogCategory::License,
            Str::contains($s, ['support']) => ServiceCatalogCategory::SupportPackage,
            Str::contains($s, ['qr']) => ServiceCatalogCategory::QrCode,
            Str::contains($s, ['mail', 'exchange', 'microsoft 365', 'm365']) => ServiceCatalogCategory::MailExchange,
            Str::contains($s, ['speicher', 'storage', 'space']) => ServiceCatalogCategory::Storage,
            Str::contains($s, ['saas', 'tool', 'software']) => ServiceCatalogCategory::ToolSaas,
            default => ServiceCatalogCategory::AdditionalService,
        };
    }
};
