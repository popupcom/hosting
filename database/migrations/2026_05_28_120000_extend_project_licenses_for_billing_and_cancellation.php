<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_licenses', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('license_product_id');
            $table->date('end_date')->nullable()->after('start_date');
            $table->date('next_renewal_date')->nullable()->after('expires_at');
            $table->date('cancellation_notice_until')->nullable()->after('next_renewal_date');
            $table->date('cancellation_date')->nullable()->after('cancellation_notice_until');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_date');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            $table->boolean('renews_automatically')->default(true)->after('cancellation_reason');
            $table->boolean('do_not_renew')->default(false)->after('renews_automatically');
            $table->string('moco_sync_status', 32)->default('not_synced')->after('status');
            $table->foreignId('billing_group_id')->nullable()->after('do_not_renew')->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();

            $table->index('next_renewal_date');
            $table->index('cancellation_date');
            $table->index('do_not_renew');
            $table->index('moco_sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('project_licenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('billing_group_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn([
                'start_date',
                'end_date',
                'next_renewal_date',
                'cancellation_notice_until',
                'cancellation_date',
                'cancelled_at',
                'cancellation_reason',
                'renews_automatically',
                'do_not_renew',
                'moco_sync_status',
            ]);
        });
    }
};
