<?php

use App\Models\ProjectService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            $table->string('name_snapshot')->nullable()->after('service_catalog_item_id');
            $table->text('description_snapshot')->nullable()->after('name_snapshot');
            $table->decimal('cost_price_snapshot', 10, 2)->nullable()->after('description_snapshot');
            $table->decimal('sales_price_snapshot', 10, 2)->nullable()->after('cost_price_snapshot');
            $table->string('billing_interval_snapshot', 32)->nullable()->after('sales_price_snapshot');

            $table->string('custom_name')->nullable()->after('billing_interval_snapshot');
            $table->text('custom_description')->nullable()->after('custom_name');
            $table->decimal('custom_quantity', 12, 4)->nullable()->after('quantity');

            $table->unsignedSmallInteger('minimum_term_months')->nullable()->after('end_date');
            $table->date('next_renewal_date')->nullable()->after('minimum_term_months');
            $table->date('cancellation_notice_until')->nullable()->after('next_renewal_date');

            $table->date('cancellation_date')->nullable()->after('cancellation_notice_until');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_date');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            $table->boolean('renews_automatically')->default(true)->after('cancellation_reason');
            $table->boolean('do_not_renew')->default(false)->after('renews_automatically');

            $table->foreignId('billing_group_id')->nullable()->after('do_not_renew')->constrained()->nullOnDelete();

            $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();

            $table->index('next_renewal_date');
            $table->index('cancellation_date');
            $table->index('do_not_renew');
            $table->index('billing_group_id');
        });

        $this->backfillSnapshots();
    }

    public function down(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('billing_group_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn([
                'name_snapshot',
                'description_snapshot',
                'cost_price_snapshot',
                'sales_price_snapshot',
                'billing_interval_snapshot',
                'custom_name',
                'custom_description',
                'custom_quantity',
                'minimum_term_months',
                'next_renewal_date',
                'cancellation_notice_until',
                'cancellation_date',
                'cancelled_at',
                'cancellation_reason',
                'renews_automatically',
                'do_not_renew',
            ]);
        });
    }

    private function backfillSnapshots(): void
    {
        if (! Schema::hasTable('project_services') || ! Schema::hasTable('service_catalog_items')) {
            return;
        }

        ProjectService::query()
            ->whereNull('name_snapshot')
            ->with('serviceCatalogItem')
            ->chunkById(100, function ($services): void {
                foreach ($services as $service) {
                    $item = $service->serviceCatalogItem;
                    if ($item === null) {
                        continue;
                    }
                    $service->forceFill([
                        'name_snapshot' => $item->name,
                        'description_snapshot' => $item->description,
                        'cost_price_snapshot' => $item->cost_price,
                        'sales_price_snapshot' => $item->sales_price,
                        'billing_interval_snapshot' => $item->billing_interval?->value,
                        'minimum_term_months' => $service->minimum_term_months ?? $item->minimum_term_months,
                    ])->saveQuietly();
                }
            });
    }
};
