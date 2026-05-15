<?php

namespace App\Services\ProjectServices;

use App\Enums\ProjectServiceStatus;
use App\Models\BillingGroupItem;
use App\Models\ProjectService;
use App\Models\ServiceCatalogItem;

final class ProjectServiceSnapshotter
{
    public static function applyCatalogSnapshots(ProjectService $service, ?ServiceCatalogItem $item = null): void
    {
        $item ??= $service->serviceCatalogItem;
        if ($item === null) {
            return;
        }

        $service->name_snapshot = $item->name;
        $service->description_snapshot = $item->description;
        $service->cost_price_snapshot = $item->cost_price;
        $service->sales_price_snapshot = $item->sales_price;
        $service->billing_interval_snapshot = $item->billing_interval?->value;

        if ($service->minimum_term_months === null && $item->minimum_term_months !== null) {
            $service->minimum_term_months = $item->minimum_term_months;
        }

        if ($service->quantity === null || (float) $service->quantity === 0.0) {
            $service->quantity = $item->default_quantity ?? 1;
        }
    }

    public static function syncBillingGroupItem(ProjectService $service): void
    {
        BillingGroupItem::query()
            ->where('billable_type', ProjectService::class)
            ->where('billable_id', $service->getKey())
            ->delete();

        if ($service->billing_group_id === null) {
            return;
        }

        BillingGroupItem::query()->create([
            'billing_group_id' => $service->billing_group_id,
            'billable_type' => ProjectService::class,
            'billable_id' => $service->getKey(),
        ]);
    }

    public static function markCancelled(ProjectService $service, ?string $reason = null): void
    {
        $service->status = ProjectServiceStatus::PendingCancellation;
        $service->do_not_renew = true;
        $service->renews_automatically = false;
        $service->cancellation_reason = $reason;
        $service->cancelled_at = now();

        if ($service->cancellation_date === null && $service->end_date !== null) {
            $service->cancellation_date = $service->end_date;
        }
    }
}
