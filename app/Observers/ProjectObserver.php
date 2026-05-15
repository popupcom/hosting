<?php

namespace App\Observers;

use App\Enums\NotificationEventKey;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\ChangeLog;
use App\Models\Project;
use App\Models\User;
use App\Services\Notifications\ChangeLogger;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Support\Facades\Auth;

class ProjectObserver
{
    /** @var list<string> */
    private const LOG_FIELDS = ['name', 'status', 'url', 'maintenance_contract', 'moco_project_id'];

    public function created(Project $project): void
    {
        $log = ChangeLogger::logEvent(
            $project,
            NotificationEventKey::ProjectCreated->value,
            newValue: $project->name,
        );

        $this->dispatch($project, NotificationEventKey::ProjectCreated->value, 'Neues Projekt', [$log]);
    }

    public function updated(Project $project): void
    {
        $logs = ChangeLogger::logChanges(
            $project,
            NotificationEventKey::ProjectUpdated->value,
            self::LOG_FIELDS,
        );

        if ($logs !== []) {
            $this->dispatch($project, NotificationEventKey::ProjectUpdated->value, 'Projekt geändert', $logs);
        }
    }

    /**
     * @param  list<ChangeLog>  $logs
     */
    private function dispatch(Project $project, string $eventKey, string $headline, array $logs): void
    {
        $project->loadMissing('client');
        $actor = Auth::user();

        NotificationDispatcher::dispatch($eventKey, $project, [
            'subject' => $headline.' – '.$project->name,
            'headline' => $headline,
            'intro' => 'Projekt „'.$project->name.'“ wurde aktualisiert.',
            'project_name' => $project->name,
            'client_name' => $project->client?->name,
            'item_label' => $project->name,
            'changes' => NotificationDispatcher::changesFromLogs($logs),
            'action_url' => ProjectResource::getUrl('edit', ['record' => $project]),
            'changed_at' => now()->format('d.m.Y H:i'),
            'changed_by' => $actor instanceof User ? $actor->name : null,
        ], $logs);
    }
}
