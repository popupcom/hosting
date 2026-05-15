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
use App\Enums\ServerStatus;
use App\Enums\ServiceCatalogBillingInterval;
use App\Enums\ServiceCatalogCategory;
use App\Enums\ServiceCatalogUnit;
use App\Enums\SupportPackageStatus;
use App\Models\ProjectDomain;
use App\Models\ProjectLicenseAssignment;
use App\Models\Server;
use App\Models\SupportPackage;
use Illuminate\Database\Eloquent\Model;

final class GermanLabels
{
    /** @return array<string, string> */
    public static function clientStatuses(): array
    {
        return [
            ClientStatus::Active->value => 'Aktiv',
            ClientStatus::Inactive->value => 'Inaktiv',
            ClientStatus::Lead->value => 'Lead',
        ];
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
        return [
            ProjectStatus::Active->value => 'Aktiv',
            ProjectStatus::Inactive->value => 'Inaktiv',
            ProjectStatus::Archived->value => 'Archiviert',
            ProjectStatus::OnHold->value => 'Pausiert',
        ];
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
        return [
            ProjectDomainStatus::Active->value => 'Aktiv',
            ProjectDomainStatus::PendingTransfer->value => 'Transfer ausstehend',
            ProjectDomainStatus::Expired->value => 'Abgelaufen',
            ProjectDomainStatus::Cancelled->value => 'Gekündigt',
        ];
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
        return [
            ServerStatus::Active->value => 'Aktiv',
            ServerStatus::Maintenance->value => 'Wartung',
            ServerStatus::Retired->value => 'Außer Betrieb',
        ];
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
        return [
            LicenseStatus::Active->value => 'Aktiv',
            LicenseStatus::Expired->value => 'Abgelaufen',
            LicenseStatus::Suspended->value => 'Ausgesetzt',
            LicenseStatus::Cancelled->value => 'Gekündigt',
        ];
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
        return [
            LicenseAssignmentStatus::Active->value => 'Aktiv',
            LicenseAssignmentStatus::PendingCancellation->value => 'Kündigung vorgemerkt',
            LicenseAssignmentStatus::Cancelled->value => 'Gekündigt',
            LicenseAssignmentStatus::Expired->value => 'Abgelaufen',
        ];
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
        return [
            BillingCadence::Monthly->value => 'Monatlich',
            BillingCadence::Yearly->value => 'Jährlich',
            BillingCadence::OneTime->value => 'Einmalig',
        ];
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
        return [
            ServiceCatalogCategory::Hosting->value => 'Hosting',
            ServiceCatalogCategory::Domain->value => 'Domain',
            ServiceCatalogCategory::Ssl->value => 'SSL',
            ServiceCatalogCategory::License->value => 'Lizenz',
            ServiceCatalogCategory::SupportPackage->value => 'Supportpaket',
            ServiceCatalogCategory::QrCode->value => 'QR-Code',
            ServiceCatalogCategory::MailExchange->value => 'Mail / Exchange',
            ServiceCatalogCategory::Storage->value => 'Speicherplatz',
            ServiceCatalogCategory::ToolSaas->value => 'Tool / SaaS',
            ServiceCatalogCategory::Monitoring->value => 'Monitoring',
            ServiceCatalogCategory::AdditionalService->value => 'Zusatzleistung',
        ];
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
        return [
            ServiceCatalogUnit::Month->value => 'Monat',
            ServiceCatalogUnit::Year->value => 'Jahr',
            ServiceCatalogUnit::Piece->value => 'Stück',
            ServiceCatalogUnit::FlatRate->value => 'pauschal',
        ];
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
        return [
            ServiceCatalogBillingInterval::Monthly->value => 'monatlich',
            ServiceCatalogBillingInterval::Yearly->value => 'jährlich',
            ServiceCatalogBillingInterval::OneTime->value => 'einmalig',
            ServiceCatalogBillingInterval::FlatRate->value => 'pauschal',
        ];
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
        return [
            ProjectServiceStatus::Active->value => 'Aktiv',
            ProjectServiceStatus::Paused->value => 'Pausiert',
            ProjectServiceStatus::PendingCancellation->value => 'Kündigung vorgemerkt',
            ProjectServiceStatus::Cancelled->value => 'Gekündigt',
            ProjectServiceStatus::Expired->value => 'Abgelaufen',
        ];
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
        return [
            ProjectServiceMocoSyncStatus::NotSynced->value => 'Nicht synchronisiert',
            ProjectServiceMocoSyncStatus::Ready->value => 'Bereit',
            ProjectServiceMocoSyncStatus::Synced->value => 'Synchronisiert',
            ProjectServiceMocoSyncStatus::Error->value => 'Fehler',
        ];
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
        return [
            LicenseSharingModel::Shared->value => 'Geteilt (shared)',
            LicenseSharingModel::Dedicated->value => 'Dediziert',
            LicenseSharingModel::SeatBased->value => 'Seat-basiert',
        ];
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
        return [
            LicenseProductStatus::Active->value => 'Aktiv',
            LicenseProductStatus::Inactive->value => 'Inaktiv',
        ];
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
        return [
            SupportPackageStatus::Active->value => 'Aktiv',
            SupportPackageStatus::Paused->value => 'Pausiert',
            SupportPackageStatus::Cancelled->value => 'Gekündigt',
            SupportPackageStatus::Expired->value => 'Abgelaufen',
        ];
    }

    public static function supportPackageStatus(?SupportPackageStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::supportPackageStatuses()[$state->value] ?? $state->value;
    }

    /** @return array<string, string> */
    public static function maintenanceTypes(): array
    {
        return [
            MaintenanceType::WordPressCore->value => 'WordPress Core',
            MaintenanceType::PluginUpdate->value => 'Plugin-Updates',
            MaintenanceType::ThemeUpdate->value => 'Theme-Updates',
            MaintenanceType::Backup->value => 'Backup',
            MaintenanceType::PerformanceCheck->value => 'Performance-Check',
            MaintenanceType::SecurityCheck->value => 'Security-Check',
            MaintenanceType::SupportPackageExcelSnapshot->value => 'Supportpaket-Import (Excel)',
        ];
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
        return [
            MocoSyncStatus::Pending->value => 'Ausstehend',
            MocoSyncStatus::Synced->value => 'Synchronisiert',
            MocoSyncStatus::Failed->value => 'Fehlgeschlagen',
            MocoSyncStatus::Skipped->value => 'Übersprungen',
        ];
    }

    public static function mocoSyncStatus(?MocoSyncStatus $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::mocoSyncStatuses()[$state->value] ?? $state->value;
    }

    public static function billableMorphType(?string $class): string
    {
        return match ($class) {
            ProjectDomain::class => 'Domain',
            Server::class => 'Server',
            ProjectLicenseAssignment::class => 'Lizenz-Zuweisung',
            SupportPackage::class => 'Supportpaket',
            null, '' => '–',
            default => class_basename($class),
        };
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
}
