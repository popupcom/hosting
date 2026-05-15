<?php

namespace App\Observers;

use App\Enums\ProjectSupportPackageStatus;
use App\Models\ProjectSupportPackage;
use App\Services\Notifications\ChangeLogger;
use App\Services\ProjectServices\ProjectServiceSnapshotter;

class ProjectSupportPackageObserver
{
    /**
     * @var list<string>
     */
    private const LOG_FIELDS = [
        'support_package_id',
        'status',
        'start_date',
        'end_date',
        'cancellation_date',
        'do_not_renew',
    ];

    public function created(ProjectSupportPackage $assignment): void
    {
        ChangeLogger::logEvent($assignment, 'project_support_package_created');
    }

    public function updated(ProjectSupportPackage $assignment): void
    {
        ChangeLogger::logChanges($assignment, 'project_support_package_updated', self::LOG_FIELDS);

        if (
            $assignment->wasChanged('status')
            && in_array($assignment->status, [ProjectSupportPackageStatus::Cancelled, ProjectSupportPackageStatus::PendingCancellation], true)
            && $assignment->projectService
        ) {
            ProjectServiceSnapshotter::markCancelled($assignment->projectService, 'Supportpaket beendet');
            $assignment->projectService->save();
        }
    }
}
