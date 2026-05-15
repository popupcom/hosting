<?php

namespace App\Services\Notifications;

use App\Enums\NotificationEventKey;
use App\Enums\ProjectServiceStatus;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\ChangeLog;
use App\Models\ProjectService;
use App\Models\User;

final class ProjectServiceNotificationBuilder
{
    /**
     * @param  list<ChangeLog>  $logs
     */
    public static function dispatchForUpdate(ProjectService $service, array $logs): void
    {
        if ($logs === []) {
            return;
        }

        $service->loadMissing(['project.client', 'updatedByUser']);
        $eventKey = self::resolveEventKey($service, $logs);

        NotificationDispatcher::dispatch(
            $eventKey->value,
            $service,
            self::context($service, $logs, $eventKey->label()),
            $logs,
        );
    }

    public static function dispatchCreated(ProjectService $service, ?ChangeLog $log = null): void
    {
        $service->loadMissing(['project.client', 'createdByUser']);

        NotificationDispatcher::dispatch(
            NotificationEventKey::ProjectServiceCreated->value,
            $service,
            self::context($service, $log ? [$log] : [], 'Neue Projekt-Leistung'),
            $log ? [$log] : null,
        );
    }

    /**
     * @param  list<ChangeLog>  $logs
     */
    private static function resolveEventKey(ProjectService $service, array $logs): NotificationEventKey
    {
        foreach ($logs as $log) {
            if ($log->field_name === 'status') {
                if (in_array($log->new_value, [
                    ProjectServiceStatus::PendingCancellation->value,
                    ProjectServiceStatus::Cancelled->value,
                    ProjectServiceStatus::Expired->value,
                ], true)) {
                    return NotificationEventKey::ProjectServiceCancelled;
                }
            }
        }

        foreach ($logs as $log) {
            if ($log->field_name === 'moco_sync_status') {
                if ($log->new_value === 'ready') {
                    return NotificationEventKey::MocoBillingReady;
                }

                return NotificationEventKey::MocoBillingChanged;
            }
        }

        return NotificationEventKey::ProjectServiceUpdated;
    }

    /**
     * @param  list<ChangeLog>  $logs
     * @return array<string, mixed>
     */
    private static function context(ProjectService $service, array $logs, string $headline): array
    {
        $actor = $service->updatedByUser ?? $service->createdByUser;

        return [
            'subject' => $headline.' – '.$service->effective_name,
            'headline' => $headline,
            'intro' => 'Projekt-Leistung „'.$service->effective_name.'“ wurde geändert.',
            'project_name' => $service->project?->name,
            'client_name' => $service->project?->client?->name,
            'item_label' => $service->effective_name,
            'changes' => NotificationDispatcher::changesFromLogs($logs),
            'action_url' => $service->project
                ? ProjectResource::getUrl('edit', ['record' => $service->project])
                : null,
            'changed_at' => now()->format('d.m.Y H:i'),
            'changed_by' => $actor instanceof User ? $actor->name : null,
        ];
    }
}
