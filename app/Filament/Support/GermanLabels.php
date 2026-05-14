<?php

namespace App\Filament\Support;

use App\Enums\ClientStatus;
use App\Enums\CostLineItemType;
use App\Enums\LicenseStatus;
use App\Enums\MaintenanceType;
use App\Enums\MocoSyncStatus;
use App\Enums\ProjectDomainStatus;
use App\Enums\ProjectStatus;
use App\Enums\ServerStatus;
use App\Enums\SupportPackageStatus;
use App\Models\License;
use App\Models\ProjectDomain;
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
    public static function costLineItemTypes(): array
    {
        return [
            CostLineItemType::Domain->value => 'Domain',
            CostLineItemType::Hosting->value => 'Hosting',
            CostLineItemType::License->value => 'Lizenz',
            CostLineItemType::SupportPackage->value => 'Supportpaket',
            CostLineItemType::AdditionalService->value => 'Zusatzleistung',
        ];
    }

    public static function costLineItemType(?CostLineItemType $state): string
    {
        if ($state === null) {
            return '–';
        }

        return self::costLineItemTypes()[$state->value] ?? $state->value;
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
            License::class => 'Lizenz',
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
            $billable instanceof License => $billable->name,
            $billable instanceof SupportPackage => $billable->name,
            default => $billable->getKey() ? (class_basename($billable).' #'.$billable->getKey()) : class_basename($billable),
        };
    }
}
