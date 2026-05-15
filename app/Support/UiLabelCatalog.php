<?php

namespace App\Support;

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

final class UiLabelCatalog
{
    public const DOMAIN_STAMMDATEN = 'stammdaten';

    public const DOMAIN_CATALOG = 'catalog';

    public const DOMAIN_SUPPORT = 'support';

    public const DOMAIN_ABRECHNUNG = 'billing';

    /**
     * @return array<string, string>
     */
    public static function domainLabels(): array
    {
        return [
            self::DOMAIN_STAMMDATEN => 'Stammdaten',
            self::DOMAIN_CATALOG => 'Leistungskatalog',
            self::DOMAIN_SUPPORT => 'Support',
            self::DOMAIN_ABRECHNUNG => 'Abrechnung',
        ];
    }

    /**
     * @return array<string, array{title: string, domain: string, fields: list<array{key: string, label: string, default: string}>}>
     */
    public static function groups(): array
    {
        return [
            // Stammdaten
            'client_statuses' => self::group(self::DOMAIN_STAMMDATEN, 'Kund:innen – Status', [
                ClientStatus::Active->value => ['Aktiv', 'Aktiv'],
                ClientStatus::Inactive->value => ['Inaktiv', 'Inaktiv'],
                ClientStatus::Lead->value => ['Lead', 'Lead'],
            ]),
            'project_statuses' => self::group(self::DOMAIN_STAMMDATEN, 'Projekte – Status', [
                ProjectStatus::Active->value => ['Aktiv', 'Aktiv'],
                ProjectStatus::Inactive->value => ['Inaktiv', 'Inaktiv'],
                ProjectStatus::Archived->value => ['Archiviert', 'Archiviert'],
                ProjectStatus::OnHold->value => ['Pausiert', 'Pausiert'],
            ]),
            'project_domain_statuses' => self::group(self::DOMAIN_STAMMDATEN, 'Domains – Status', [
                ProjectDomainStatus::Active->value => ['Aktiv', 'Aktiv'],
                ProjectDomainStatus::PendingTransfer->value => ['Transfer ausstehend', 'Transfer ausstehend'],
                ProjectDomainStatus::Expired->value => ['Abgelaufen', 'Abgelaufen'],
                ProjectDomainStatus::Cancelled->value => ['Gekündigt', 'Gekündigt'],
            ]),
            'server_statuses' => self::group(self::DOMAIN_STAMMDATEN, 'Server – Status', [
                ServerStatus::Active->value => ['Aktiv', 'Aktiv'],
                ServerStatus::Maintenance->value => ['Wartung', 'Wartung'],
                ServerStatus::Retired->value => ['Außer Betrieb', 'Außer Betrieb'],
            ]),

            // Leistungskatalog (inkl. Lizenzen)
            'service_catalog_categories' => self::group(self::DOMAIN_CATALOG, 'Kategorien', [
                ServiceCatalogCategory::Hosting->value => ['Hosting', 'Hosting'],
                ServiceCatalogCategory::Domain->value => ['Domain', 'Domain'],
                ServiceCatalogCategory::Ssl->value => ['SSL', 'SSL'],
                ServiceCatalogCategory::License->value => ['Lizenz', 'Lizenz'],
                ServiceCatalogCategory::SupportPackage->value => ['Supportpaket', 'Supportpaket'],
                ServiceCatalogCategory::QrCode->value => ['QR-Code', 'QR-Code'],
                ServiceCatalogCategory::MailExchange->value => ['Mail / Exchange', 'Mail / Exchange'],
                ServiceCatalogCategory::Storage->value => ['Speicherplatz', 'Speicherplatz'],
                ServiceCatalogCategory::ToolSaas->value => ['Tool / SaaS', 'Tool / SaaS'],
                ServiceCatalogCategory::Monitoring->value => ['Monitoring', 'Monitoring'],
                ServiceCatalogCategory::AdditionalService->value => ['Zusatzleistung', 'Zusatzleistung'],
            ]),
            'service_catalog_units' => self::group(self::DOMAIN_CATALOG, 'Einheiten', [
                ServiceCatalogUnit::Month->value => ['Monat', 'Monat'],
                ServiceCatalogUnit::Year->value => ['Jahr', 'Jahr'],
                ServiceCatalogUnit::Piece->value => ['Stück', 'Stück'],
                ServiceCatalogUnit::FlatRate->value => ['pauschal', 'pauschal'],
            ]),
            'service_catalog_billing_intervals' => self::group(self::DOMAIN_CATALOG, 'Abrechnungsintervalle', [
                ServiceCatalogBillingInterval::Monthly->value => ['monatlich', 'monatlich'],
                ServiceCatalogBillingInterval::Yearly->value => ['jährlich', 'jährlich'],
                ServiceCatalogBillingInterval::OneTime->value => ['einmalig', 'einmalig'],
                ServiceCatalogBillingInterval::FlatRate->value => ['pauschal', 'pauschal'],
            ]),
            'project_service_statuses' => self::group(self::DOMAIN_CATALOG, 'Projekt-Leistungen – Status', [
                ProjectServiceStatus::Active->value => ['Aktiv', 'Aktiv'],
                ProjectServiceStatus::Paused->value => ['Pausiert', 'Pausiert'],
                ProjectServiceStatus::PendingCancellation->value => ['Kündigung vorgemerkt', 'Kündigung vorgemerkt'],
                ProjectServiceStatus::Cancelled->value => ['Gekündigt', 'Gekündigt'],
                ProjectServiceStatus::Expired->value => ['Abgelaufen', 'Abgelaufen'],
            ]),
            'project_service_moco_sync_statuses' => self::group(self::DOMAIN_CATALOG, 'Projekt-Leistungen – Moco-Sync', [
                ProjectServiceMocoSyncStatus::NotSynced->value => ['Nicht synchronisiert', 'Nicht synchronisiert'],
                ProjectServiceMocoSyncStatus::Ready->value => ['Bereit', 'Bereit'],
                ProjectServiceMocoSyncStatus::Synced->value => ['Synchronisiert', 'Synchronisiert'],
                ProjectServiceMocoSyncStatus::Error->value => ['Fehler', 'Fehler'],
            ]),
            'license_statuses' => self::group(self::DOMAIN_CATALOG, 'Lizenzen – Status', [
                LicenseStatus::Active->value => ['Aktiv', 'Aktiv'],
                LicenseStatus::Expired->value => ['Abgelaufen', 'Abgelaufen'],
                LicenseStatus::Suspended->value => ['Ausgesetzt', 'Ausgesetzt'],
                LicenseStatus::Cancelled->value => ['Gekündigt', 'Gekündigt'],
            ]),
            'license_assignment_statuses' => self::group(self::DOMAIN_CATALOG, 'Lizenz-Zuweisungen – Status', [
                LicenseAssignmentStatus::Active->value => ['Aktiv', 'Aktiv'],
                LicenseAssignmentStatus::PendingCancellation->value => ['Kündigung vorgemerkt', 'Kündigung vorgemerkt'],
                LicenseAssignmentStatus::Cancelled->value => ['Gekündigt', 'Gekündigt'],
                LicenseAssignmentStatus::Expired->value => ['Abgelaufen', 'Abgelaufen'],
            ]),
            'license_sharing_models' => self::group(self::DOMAIN_CATALOG, 'Lizenzprodukte – Lizenzmodell', [
                LicenseSharingModel::Shared->value => ['Shared', 'Shared'],
                LicenseSharingModel::Dedicated->value => ['Dedicated', 'Dedicated'],
                LicenseSharingModel::SeatBased->value => ['Seat-basiert', 'Seat-basiert'],
            ]),
            'license_product_statuses' => self::group(self::DOMAIN_CATALOG, 'Lizenzprodukte – Status', [
                LicenseProductStatus::Active->value => ['Aktiv', 'Aktiv'],
                LicenseProductStatus::Inactive->value => ['Inaktiv', 'Inaktiv'],
            ]),

            // Support (Supportpakete, Wartung, ToDos)
            'support_package_statuses' => self::group(self::DOMAIN_SUPPORT, 'Supportpakete – Status', [
                SupportPackageStatus::Active->value => ['Aktiv', 'Aktiv'],
                SupportPackageStatus::Paused->value => ['Pausiert', 'Pausiert'],
                SupportPackageStatus::Cancelled->value => ['Gekündigt', 'Gekündigt'],
                SupportPackageStatus::Expired->value => ['Abgelaufen', 'Abgelaufen'],
            ]),
            'project_support_package_statuses' => self::group(self::DOMAIN_SUPPORT, 'Projekt-Supportpakete – Status', [
                ProjectSupportPackageStatus::Active->value => ['Aktiv', 'Aktiv'],
                ProjectSupportPackageStatus::PendingCancellation->value => ['Kündigung vorgemerkt', 'Kündigung vorgemerkt'],
                ProjectSupportPackageStatus::Cancelled->value => ['Gekündigt', 'Gekündigt'],
                ProjectSupportPackageStatus::Expired->value => ['Abgelaufen', 'Abgelaufen'],
            ]),
            'maintenance_types' => self::group(self::DOMAIN_SUPPORT, 'Wartungsprotokolle – Arten', [
                MaintenanceType::WordPressCore->value => ['WordPress Core', 'WordPress Core'],
                MaintenanceType::PluginUpdate->value => ['Plugin-Updates', 'Plugin-Updates'],
                MaintenanceType::ThemeUpdate->value => ['Theme-Updates', 'Theme-Updates'],
                MaintenanceType::Backup->value => ['Backup', 'Backup'],
                MaintenanceType::PerformanceCheck->value => ['Performance-Check', 'Performance-Check'],
                MaintenanceType::SecurityCheck->value => ['Security-Check', 'Security-Check'],
                MaintenanceType::SupportPackageExcelSnapshot->value => ['Supportpaket-Import (Excel)', 'Supportpaket-Import (Excel)'],
            ]),
            'todo_statuses' => self::group(self::DOMAIN_SUPPORT, 'ToDos – Status', [
                ReminderStatus::Pending->value => ['Offen', 'Offen'],
                ReminderStatus::Overdue->value => ['Überfällig', 'Überfällig'],
                ReminderStatus::Snoozed->value => ['Zurückgestellt', 'Zurückgestellt'],
                ReminderStatus::Completed->value => ['Erledigt', 'Erledigt'],
                ReminderStatus::Cancelled->value => ['Abgebrochen', 'Abgebrochen'],
            ]),
            'todo_remindable_types' => self::group(self::DOMAIN_SUPPORT, 'ToDos – Bezugstypen', [
                ProjectDomain::class => ['Domain', 'Domain'],
                Server::class => ['Server', 'Server'],
                MaintenanceHistory::class => ['Wartung', 'Wartung'],
            ]),

            // Abrechnung
            'billing_cadences' => self::group(self::DOMAIN_ABRECHNUNG, 'Verrechnungsrhythmus', [
                BillingCadence::Monthly->value => ['Monatlich', 'Monatlich'],
                BillingCadence::Yearly->value => ['Jährlich', 'Jährlich'],
                BillingCadence::OneTime->value => ['Einmalig', 'Einmalig'],
            ]),
            'moco_sync_statuses' => self::group(self::DOMAIN_ABRECHNUNG, 'Moco – Sync-Status', [
                MocoSyncStatus::Pending->value => ['Ausstehend', 'Ausstehend'],
                MocoSyncStatus::Synced->value => ['Synchronisiert', 'Synchronisiert'],
                MocoSyncStatus::Failed->value => ['Fehlgeschlagen', 'Fehlgeschlagen'],
                MocoSyncStatus::Skipped->value => ['Übersprungen', 'Übersprungen'],
            ]),
            'billable_morph_types' => self::group(self::DOMAIN_ABRECHNUNG, 'Verrechenbar – Bezugstypen', [
                ProjectDomain::class => ['Domain', 'Domain'],
                Server::class => ['Server', 'Server'],
                ProjectLicenseAssignment::class => ['Lizenz-Zuweisung', 'Lizenz-Zuweisung'],
                SupportPackage::class => ['Supportpaket', 'Supportpaket'],
            ]),
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function groupedSelectOptions(): array
    {
        $grouped = [];

        foreach (self::domainLabels() as $domainKey => $domainLabel) {
            foreach (self::groups() as $groupKey => $group) {
                if ($group['domain'] !== $domainKey) {
                    continue;
                }

                $grouped[$domainLabel][$groupKey] = $group['title'];
            }
        }

        return array_filter($grouped, fn (array $options): bool => $options !== []);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function defaults(): array
    {
        $defaults = [];

        foreach (self::groups() as $groupKey => $group) {
            foreach ($group['fields'] as $field) {
                $defaults[$groupKey][$field['key']] = $field['default'];
            }
        }

        return $defaults;
    }

    /**
     * @param  array<string|int, array{0: string, 1: string}|list{string}>  $fields
     * @return array{title: string, domain: string, fields: list<array{key: string, label: string, default: string}>}
     */
    private static function group(string $domain, string $title, array $fields): array
    {
        $normalized = [];

        foreach ($fields as $key => $field) {
            $normalized[] = [
                'key' => (string) $key,
                'label' => $field[0],
                'default' => $field[1],
            ];
        }

        return [
            'title' => $title,
            'domain' => $domain,
            'fields' => $normalized,
        ];
    }
}
