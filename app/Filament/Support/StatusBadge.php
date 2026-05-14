<?php

namespace App\Filament\Support;

use App\Enums\ClientStatus;
use App\Enums\CostLineItemType;
use App\Enums\LicenseStatus;
use App\Enums\MaintenanceType;
use App\Enums\MocoSyncStatus;
use App\Enums\ProjectDomainStatus;
use App\Enums\ProjectStatus;
use App\Enums\ReminderStatus;
use App\Enums\ServerStatus;
use App\Enums\SupportPackageStatus;

final class StatusBadge
{
    public static function client(?ClientStatus $state): string
    {
        return match ($state) {
            ClientStatus::Active => 'success',
            ClientStatus::Inactive => 'gray',
            ClientStatus::Lead => 'warning',
            default => 'gray',
        };
    }

    public static function project(?ProjectStatus $state): string
    {
        return match ($state) {
            ProjectStatus::Active => 'success',
            ProjectStatus::Inactive => 'gray',
            ProjectStatus::Archived => 'danger',
            ProjectStatus::OnHold => 'warning',
            default => 'gray',
        };
    }

    public static function projectDomain(?ProjectDomainStatus $state): string
    {
        return match ($state) {
            ProjectDomainStatus::Active => 'success',
            ProjectDomainStatus::PendingTransfer => 'warning',
            ProjectDomainStatus::Expired => 'danger',
            ProjectDomainStatus::Cancelled => 'gray',
            default => 'gray',
        };
    }

    public static function server(?ServerStatus $state): string
    {
        return match ($state) {
            ServerStatus::Active => 'success',
            ServerStatus::Maintenance => 'warning',
            ServerStatus::Retired => 'gray',
            default => 'gray',
        };
    }

    public static function license(?LicenseStatus $state): string
    {
        return match ($state) {
            LicenseStatus::Active => 'success',
            LicenseStatus::Expired => 'danger',
            LicenseStatus::Suspended => 'warning',
            LicenseStatus::Cancelled => 'gray',
            default => 'gray',
        };
    }

    public static function supportPackage(?SupportPackageStatus $state): string
    {
        return match ($state) {
            SupportPackageStatus::Active => 'success',
            SupportPackageStatus::Paused => 'warning',
            SupportPackageStatus::Cancelled => 'gray',
            SupportPackageStatus::Expired => 'danger',
            default => 'gray',
        };
    }

    public static function maintenanceType(?MaintenanceType $state): string
    {
        return match ($state) {
            MaintenanceType::SecurityCheck, MaintenanceType::Backup => 'info',
            MaintenanceType::WordPressCore, MaintenanceType::PluginUpdate, MaintenanceType::ThemeUpdate => 'primary',
            MaintenanceType::PerformanceCheck => 'warning',
            default => 'gray',
        };
    }

    public static function costLineType(?CostLineItemType $state): string
    {
        return match ($state) {
            CostLineItemType::Domain => 'info',
            CostLineItemType::Hosting => 'warning',
            CostLineItemType::License => 'primary',
            CostLineItemType::SupportPackage => 'success',
            CostLineItemType::AdditionalService => 'gray',
            default => 'gray',
        };
    }

    public static function mocoSync(?MocoSyncStatus $state): string
    {
        return match ($state) {
            MocoSyncStatus::Synced => 'success',
            MocoSyncStatus::Pending => 'warning',
            MocoSyncStatus::Failed => 'danger',
            MocoSyncStatus::Skipped => 'gray',
            default => 'gray',
        };
    }

    public static function reminder(?ReminderStatus $state): string
    {
        return match ($state) {
            ReminderStatus::Completed => 'success',
            ReminderStatus::Pending => 'warning',
            ReminderStatus::Overdue => 'danger',
            ReminderStatus::Snoozed => 'gray',
            ReminderStatus::Cancelled => 'gray',
            default => 'gray',
        };
    }
}
