<?php

namespace App\Enums;

enum NotificationEventKey: string
{
    case ProjectServiceCreated = 'project_service_created';
    case ProjectServiceUpdated = 'project_service_updated';
    case ProjectServiceCancelled = 'project_service_cancelled';
    case SslRenewalRequired = 'ssl_renewal_required';
    case DomainRenewalRequired = 'domain_renewal_required';
    case DomainCancelled = 'domain_cancelled';
    case LicenseAssignmentExpiring = 'license_assignment_expiring';
    case SupportUpdateDue = 'support_update_due';
    case SupportUpdateOverdue = 'support_update_overdue';
    case MocoBillingReady = 'moco_billing_ready';
    case MocoBillingChanged = 'moco_billing_changed';
    case HostingChanged = 'hosting_changed';
    case ServerExpiring = 'server_expiring';
    case ProjectCreated = 'project_created';
    case ProjectUpdated = 'project_updated';
    case TodoCreated = 'todo_created';
    case TodoUpdated = 'todo_updated';
    case TodoCompleted = 'todo_completed';
    case TodoOverdue = 'todo_overdue';

    public function label(): string
    {
        return match ($this) {
            self::ProjectServiceCreated => 'Leistung hinzugefügt',
            self::ProjectServiceUpdated => 'Leistung geändert',
            self::ProjectServiceCancelled => 'Leistung gekündigt',
            self::SslRenewalRequired => 'SSL-Verlängerung erforderlich',
            self::DomainRenewalRequired => 'Domain-Verlängerung erforderlich',
            self::DomainCancelled => 'Domain gekündigt',
            self::LicenseAssignmentExpiring => 'Lizenz-Zuweisung läuft aus',
            self::SupportUpdateDue => 'Support-Update fällig',
            self::SupportUpdateOverdue => 'Support-Update überfällig',
            self::MocoBillingReady => 'Moco: abrechnungsbereit',
            self::MocoBillingChanged => 'Moco: Status geändert',
            self::HostingChanged => 'Hosting geändert',
            self::ServerExpiring => 'Server läuft aus',
            self::ProjectCreated => 'Projekt angelegt',
            self::ProjectUpdated => 'Projekt geändert',
            self::TodoCreated => 'Neues ToDo erhalten',
            self::TodoUpdated => 'ToDo wurde aktualisiert',
            self::TodoCompleted => 'ToDo wurde erledigt',
            self::TodoOverdue => 'ToDo ist überfällig',
        };
    }

    public function category(): NotificationEventCategory
    {
        return match ($this) {
            self::ProjectServiceCreated,
            self::ProjectServiceUpdated,
            self::ProjectServiceCancelled,
            self::MocoBillingReady,
            self::MocoBillingChanged => NotificationEventCategory::Billing,

            self::SslRenewalRequired,
            self::HostingChanged,
            self::ServerExpiring => NotificationEventCategory::Hosting,

            self::DomainRenewalRequired,
            self::DomainCancelled => NotificationEventCategory::Domain,

            self::LicenseAssignmentExpiring => NotificationEventCategory::License,

            self::SupportUpdateDue,
            self::SupportUpdateOverdue => NotificationEventCategory::Support,

            self::ProjectCreated,
            self::ProjectUpdated => NotificationEventCategory::Management,

            self::TodoCreated,
            self::TodoUpdated,
            self::TodoCompleted,
            self::TodoOverdue => NotificationEventCategory::Todo,
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ProjectServiceCreated => 'Neue abrechenbare Projekt-Leistung wurde angelegt.',
            self::ProjectServiceUpdated => 'Preis, Intervall oder andere Leistungsdaten wurden geändert.',
            self::ProjectServiceCancelled => 'Eine Projekt-Leistung wurde gekündigt oder endet.',
            self::SslRenewalRequired => 'SSL-Zertifikat muss verlängert werden.',
            self::DomainRenewalRequired => 'Domain muss verlängert werden.',
            self::DomainCancelled => 'Domain wurde gekündigt.',
            self::LicenseAssignmentExpiring => 'Lizenz-Zuweisung läuft in Kürze aus.',
            self::SupportUpdateDue => 'Geplantes Support-Update steht an.',
            self::SupportUpdateOverdue => 'Support-Update ist überfällig.',
            self::MocoBillingReady => 'Leistung ist für Moco-Abrechnung bereit.',
            self::MocoBillingChanged => 'Moco-Synchronisationsstatus wurde geändert.',
            self::HostingChanged => 'Hosting-Konfiguration wurde geändert.',
            self::ServerExpiring => 'Server-Vertrag oder -Laufzeit endet bald.',
            self::ProjectCreated => 'Neues Projekt wurde angelegt.',
            self::ProjectUpdated => 'Projektdaten wurden geändert.',
            self::TodoCreated => 'Ein neues ToDo wurde angelegt oder zugewiesen.',
            self::TodoUpdated => 'Ein ToDo wurde geändert (Fälligkeit, Status oder Nachricht).',
            self::TodoCompleted => 'Ein ToDo wurde als erledigt markiert.',
            self::TodoOverdue => 'Ein ToDo hat das Fälligkeitsdatum überschritten.',
        };
    }
}
