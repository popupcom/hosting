<?php

namespace Database\Seeders;

use App\Enums\NotificationEventKey;
use App\Models\NotificationEventType;
use App\Models\NotificationGroup;
use App\Models\NotificationGroupEventSetting;
use Illuminate\Database\Seeder;

class NotificationSystemSeeder extends Seeder
{
    public function run(): void
    {
        foreach (NotificationEventKey::cases() as $case) {
            NotificationEventType::query()->updateOrCreate(
                ['key' => $case->value],
                [
                    'name' => $case->label(),
                    'description' => $case->description(),
                    'category' => $case->category()->value,
                    'is_active' => true,
                ],
            );
        }

        $groups = [
            'Buchhaltung' => [
                'description' => 'Abrechnung, Preise, Moco, Leistungen',
                'events' => [
                    NotificationEventKey::ProjectServiceCreated,
                    NotificationEventKey::ProjectServiceUpdated,
                    NotificationEventKey::ProjectServiceCancelled,
                    NotificationEventKey::MocoBillingReady,
                    NotificationEventKey::MocoBillingChanged,
                ],
            ],
            'Technik' => [
                'description' => 'Hosting, Domains, SSL, Server',
                'events' => [
                    NotificationEventKey::SslRenewalRequired,
                    NotificationEventKey::DomainRenewalRequired,
                    NotificationEventKey::DomainCancelled,
                    NotificationEventKey::HostingChanged,
                    NotificationEventKey::ServerExpiring,
                    NotificationEventKey::ProjectServiceCancelled,
                ],
            ],
            'Support' => [
                'description' => 'Supportpakete und Wartung',
                'events' => [
                    NotificationEventKey::SupportUpdateDue,
                    NotificationEventKey::SupportUpdateOverdue,
                ],
            ],
            'Geschäftsführung' => [
                'description' => 'Management-Übersicht',
                'events' => [
                    NotificationEventKey::ProjectCreated,
                    NotificationEventKey::ProjectUpdated,
                    NotificationEventKey::ProjectServiceCancelled,
                    NotificationEventKey::LicenseAssignmentExpiring,
                ],
            ],
        ];

        $eventTypes = NotificationEventType::query()->pluck('id', 'key');

        foreach ($groups as $name => $config) {
            $group = NotificationGroup::query()->updateOrCreate(
                ['name' => $name],
                [
                    'description' => $config['description'],
                    'is_active' => true,
                ],
            );

            foreach (NotificationEventKey::cases() as $case) {
                $enabled = in_array($case, $config['events'], true);

                NotificationGroupEventSetting::query()->updateOrCreate(
                    [
                        'notification_group_id' => $group->id,
                        'notification_event_type_id' => $eventTypes[$case->value],
                    ],
                    [
                        'is_enabled' => $enabled,
                        'send_email' => $enabled,
                        'send_in_app' => $enabled,
                    ],
                );
            }
        }
    }
}
