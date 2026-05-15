<?php

namespace App\Filament\Support;

use App\Enums\BillingCadence;
use App\Enums\ClientStatus;
use App\Enums\LicenseAssignmentStatus;
use App\Enums\LicenseProductStatus;
use App\Enums\LicenseSharingModel;
use App\Enums\LicenseStatus;
use App\Enums\MaintenanceType;
use App\Enums\MocoSyncStatus;
use App\Enums\ProjectDomainStatus;
use App\Enums\ProjectServiceMocoSyncStatus;
use App\Enums\ProjectServiceStatus;
use App\Enums\ProjectStatus;
use App\Enums\ProjectSupportPackageStatus;
use App\Enums\ReminderStatus;
use App\Enums\ServerStatus;
use App\Enums\ServiceCatalogBillingInterval;
use App\Enums\ServiceCatalogCategory;
use App\Enums\ServiceCatalogUnit;
use App\Enums\SupportPackageStatus;
use App\Models\MaintenanceHistory;
use App\Models\ProjectDomain;
use App\Models\ProjectLicenseAssignment;
use App\Models\Server;
use App\Models\SupportPackage;
use App\Support\UiLabelCatalog;
use App\Support\UiLabelResolver;
use Illuminate\Database\Eloquent\Model;

final class GermanLabels
{
    /** @return array<string, string> */
    public static function clientStatuses(): array
    {
        return UiLabelResolver::merge('client_statuses', UiLabelCatalog::defaults()['client_statuses']);
    }

    public static function clientStatus(?ClientStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::clientStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function projectStatuses(): array
    {
        return UiLabelResolver::merge('project_statuses', UiLabelCatalog::defaults()['project_statuses']);
    }

    public static function projectStatus(?ProjectStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::projectStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function projectDomainStatuses(): array
    {
        return UiLabelResolver::merge('project_domain_statuses', UiLabelCatalog::defaults()['project_domain_statuses']);
    }

    public static function projectDomainStatus(?ProjectDomainStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::projectDomainStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function serverStatuses(): array
    {
        return UiLabelResolver::merge('server_statuses', UiLabelCatalog::defaults()['server_statuses']);
    }

    public static function serverStatus(?ServerStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::serverStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function licenseStatuses(): array
    {
        return UiLabelResolver::merge('license_statuses', UiLabelCatalog::defaults()['license_statuses']);
    }

    public static function licenseStatus(?LicenseStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::licenseStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function licenseAssignmentStatuses(): array
    {
        return UiLabelResolver::merge('license_assignment_statuses', UiLabelCatalog::defaults()['license_assignment_statuses']);
    }

    public static function licenseAssignmentStatus(?LicenseAssignmentStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::licenseAssignmentStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function billingCadences(): array
    {
        return UiLabelResolver::merge('billing_cadences', UiLabelCatalog::defaults()['billing_cadences']);
    }

    public static function billingCadence(?BillingCadence $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::billingCadences()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function serviceCatalogCategories(): array
    {
        return UiLabelResolver::merge('service_catalog_categories', UiLabelCatalog::defaults()['service_catalog_categories']);
    }

    public static function serviceCatalogCategory(?ServiceCatalogCategory $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::serviceCatalogCategories()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function serviceCatalogUnits(): array
    {
        return UiLabelResolver::merge('service_catalog_units', UiLabelCatalog::defaults()['service_catalog_units']);
    }

    public static function serviceCatalogUnit(?ServiceCatalogUnit $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::serviceCatalogUnits()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function serviceCatalogBillingIntervals(): array
    {
        return UiLabelResolver::merge('service_catalog_billing_intervals', UiLabelCatalog::defaults()['service_catalog_billing_intervals']);
    }

    public static function serviceCatalogBillingInterval(?ServiceCatalogBillingInterval $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::serviceCatalogBillingIntervals()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function projectServiceStatuses(): array
    {
        return UiLabelResolver::merge('project_service_statuses', UiLabelCatalog::defaults()['project_service_statuses']);
    }

    public static function projectServiceStatus(?ProjectServiceStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::projectServiceStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function projectServiceMocoSyncStatuses(): array
    {
        return UiLabelResolver::merge('project_service_moco_sync_statuses', UiLabelCatalog::defaults()['project_service_moco_sync_statuses']);
    }

    public static function projectServiceMocoSyncStatus(?ProjectServiceMocoSyncStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::projectServiceMocoSyncStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function licenseSharingModels(): array
    {
        return UiLabelResolver::merge('license_sharing_models', UiLabelCatalog::defaults()['license_sharing_models']);
    }

    public static function licenseSharingModel(?LicenseSharingModel $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::licenseSharingModels()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function licenseProductStatuses(): array
    {
        return UiLabelResolver::merge('license_product_statuses', UiLabelCatalog::defaults()['license_product_statuses']);
    }

    public static function licenseProductStatus(?LicenseProductStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::licenseProductStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function supportPackageStatuses(): array
    {
        return UiLabelResolver::merge('support_package_statuses', UiLabelCatalog::defaults()['support_package_statuses']);
    }

    public static function supportPackageStatus(?SupportPackageStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::supportPackageStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function projectSupportPackageStatuses(): array
    {
        return UiLabelResolver::merge('project_support_package_statuses', UiLabelCatalog::defaults()['project_support_package_statuses']);
    }

    public static function projectSupportPackageStatus(?ProjectSupportPackageStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::projectSupportPackageStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function maintenanceTypes(): array
    {
        return UiLabelResolver::merge('maintenance_types', UiLabelCatalog::defaults()['maintenance_types']);
    }

    public static function maintenanceType(?MaintenanceType $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::maintenanceTypes()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function mocoSyncStatuses(): array
    {
        return UiLabelResolver::merge('moco_sync_statuses', UiLabelCatalog::defaults()['moco_sync_statuses']);
    }

    public static function mocoSyncStatus(?MocoSyncStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::mocoSyncStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function billableMorphTypes(): array
    {
        return UiLabelResolver::merge('billable_morph_types', UiLabelCatalog::defaults()['billable_morph_types']);
    }

    public static function billableMorphType(?string $class): string
    {
        if ($class === null || $class === '') {
            return '–';
        }

        return self::billableMorphTypes()[$class] ?? class_basename($class);
    }

    public static function billableTitle(?Model $billable): string
    {
        if ($billable === null) {
            return '–';
        }

        return match (true) {
            $billable instanceof ProjectDomain => $billable->domain_name,
            $billable instanceof Server => $billable->name,
            $billable instanceof ProjectLicenseAssignment => $billable->licenseProduct?->name ?? ('Lizenz-Zuweisung #'.$billable->getKey()),
            $billable instanceof SupportPackage => $billable->name,
            default => $billable->getKey() ? (class_basename($billable).' #'.$billable->getKey()) : class_basename($billable),
        };
    }

    /** @return array<string, string> */
    public static function todoStatuses(): array
    {
        return UiLabelResolver::merge('todo_statuses', UiLabelCatalog::defaults()['todo_statuses']);
    }

    public static function todoStatus(?ReminderStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::todoStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<class-string<Model>, string> */
    public static function todoRemindableTypes(): array
    {
        /** @var array<class-string<Model>, string> $types */
        $types = UiLabelResolver::merge('todo_remindable_types', UiLabelCatalog::defaults()['todo_remindable_types']);

        return $types;
    }

    public static function todoRemindableType(?string $class): string
    {
        if ($class === null || $class === '') {
            return '–';
        }

        return self::todoRemindableTypes()[$class] ?? class_basename($class);
    }

    public static function todoRemindableTitle(?Model $remindable): string
    {
        if ($remindable === null) {
            return '–';
        }

        return match (true) {
            $remindable instanceof ProjectDomain => $remindable->domain_name,
            $remindable instanceof Server => $remindable->name,
            $remindable instanceof MaintenanceHistory => ($remindable->project?->name ?? 'Wartung').' · '.self::maintenanceType($remindable->maintenance_type),
            default => $remindable->getKey() ? (class_basename($remindable).' #'.$remindable->getKey()) : class_basename($remindable),
        };
    }
}
