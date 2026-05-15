<?php

namespace App\Observers;

use App\Enums\NotificationEventKey;
use App\Models\ProjectService;
use App\Services\Notifications\ChangeLogger;
use App\Services\Notifications\ProjectServiceNotificationBuilder;

class ProjectServiceObserver
{
    public function created(ProjectService $service): void
    {
        $log = ChangeLogger::logEvent(
            $service,
            NotificationEventKey::ProjectServiceCreated->value,
            fieldName: null,
            oldValue: null,
            newValue: $service->effective_name,
        );

        ProjectServiceNotificationBuilder::dispatchCreated($service, $log);
    }

    public function updated(ProjectService $service): void
    {
        $fields = config('notifications.project_service_log_fields', []);

        $logs = ChangeLogger::logChanges(
            $service,
            NotificationEventKey::ProjectServiceUpdated->value,
            $fields,
        );

        ProjectServiceNotificationBuilder::dispatchForUpdate($service, $logs);
    }
}
