<?php

namespace App\Services\SupportPackages;

use App\Enums\ProjectServiceMocoSyncStatus;
use App\Enums\ProjectServiceStatus;
use App\Enums\ProjectSupportPackageStatus;
use App\Enums\ServiceCatalogBillingInterval;
use App\Models\ProjectService;
use App\Models\ProjectSupportPackage;
use App\Models\SupportPackage;
use App\Services\Notifications\ChangeLogger;
use App\Services\ProjectServices\ProjectServiceSnapshotter;
use Illuminate\Support\Facades\DB;

final class SupportPackageProjectServiceProvisioner
{
    public static function syncActiveAssignment(ProjectSupportPackage $assignment): void
    {
        DB::transaction(function () use ($assignment): void {
            self::deactivateOtherAssignments($assignment);

            $assignment->loadMissing(['supportPackage.serviceCatalogItem', 'project']);
            $package = $assignment->supportPackage;
            $catalogItem = $package?->serviceCatalogItem;

            if ($package === null || $catalogItem === null) {
                return;
            }

            $service = self::resolveOrCreateProjectService($assignment, $package);

            if ($assignment->project_service_id !== $service->getKey()) {
                $assignment->project_service_id = $service->getKey();
                $assignment->saveQuietly();
            }
        });
    }

    public static function deactivateOtherAssignments(ProjectSupportPackage $assignment): void
    {
        ProjectSupportPackage::query()
            ->where('project_id', $assignment->project_id)
            ->whereKeyNot($assignment->getKey())
            ->where('status', ProjectSupportPackageStatus::Active)
            ->each(function (ProjectSupportPackage $other): void {
                $other->status = ProjectSupportPackageStatus::PendingCancellation;
                $other->save();

                if ($other->projectService) {
                    ProjectServiceSnapshotter::markCancelled($other->projectService, 'Supportpaket gewechselt');
                    $other->projectService->save();
                }

                ChangeLogger::logEvent(
                    $other,
                    'project_support_package_replaced',
                    'status',
                    ProjectSupportPackageStatus::Active->value,
                    ProjectSupportPackageStatus::PendingCancellation->value,
                );
            });
    }

    private static function resolveOrCreateProjectService(
        ProjectSupportPackage $assignment,
        SupportPackage $package,
    ): ProjectService {
        $catalogItem = $package->serviceCatalogItem;
        $catalogItemId = $catalogItem->getKey();

        $existing = ProjectService::query()
            ->where('project_id', $assignment->project_id)
            ->where('service_catalog_item_id', $catalogItemId)
            ->whereIn('status', [
                ProjectServiceStatus::Active,
                ProjectServiceStatus::PendingCancellation,
            ])
            ->first();

        if ($existing !== null) {
            self::applyYearlyBillingSnapshots($existing, $package);

            if ($existing->status === ProjectServiceStatus::PendingCancellation) {
                $existing->status = ProjectServiceStatus::Active;
                $existing->do_not_renew = false;
                $existing->save();
            }

            return $existing;
        }

        $monthlyVk = (float) ($catalogItem->sales_price ?? 0);
        $yearlyVk = round($monthlyVk * 12, 2);

        $service = new ProjectService([
            'project_id' => $assignment->project_id,
            'service_catalog_item_id' => $catalogItemId,
            'quantity' => 1,
            'start_date' => $assignment->start_date,
            'minimum_term_months' => $package->minimum_term_months,
            'status' => ProjectServiceStatus::Active,
            'moco_sync_status' => ProjectServiceMocoSyncStatus::Ready,
            'notes' => 'Automatisch aus Supportpaket „'.$package->name.'“ erzeugt.',
        ]);

        ProjectServiceSnapshotter::applyCatalogSnapshots($service, $catalogItem);
        self::applyYearlyBillingSnapshots($service, $package, $yearlyVk);
        $service->save();

        ChangeLogger::logEvent(
            $assignment,
            'project_support_package_service_created',
            'project_service_id',
            null,
            (string) $service->getKey(),
        );

        return $service;
    }

    private static function applyYearlyBillingSnapshots(
        ProjectService $service,
        SupportPackage $package,
        ?float $yearlyVk = null,
    ): void {
        $monthlyVk = (float) ($package->serviceCatalogItem?->sales_price ?? $service->sales_price_snapshot ?? 0);
        $yearlyVk ??= round($monthlyVk * 12, 2);

        $service->billing_interval_snapshot = ServiceCatalogBillingInterval::Yearly;
        $service->sales_price_snapshot = $yearlyVk;
        $service->quantity = $package->bill_yearly_in_advance ? 1 : ($service->quantity ?? 1);

        if ($package->bill_yearly_in_advance) {
            $service->custom_quantity = null;
        }
    }
}
