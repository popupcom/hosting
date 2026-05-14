<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProjectRequest;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Project::query()->with(['client', 'integrationSyncStates']);

        if ($clientId = $request->query('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('url', 'like', '%'.$search.'%')
                    ->orWhere('moco_project_id', $search)
                    ->orWhere('managewp_site_id', $search);
            });
        }

        return ProjectResource::collection($query->orderBy('name')->paginate($request->integer('per_page', 25)));
    }

    public function show(Project $project): ProjectResource
    {
        $project->load(['client', 'integrationSyncStates']);

        return new ProjectResource($project);
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $project->update($request->validated());
        $project->load(['client', 'integrationSyncStates']);

        return new ProjectResource($project);
    }
}
