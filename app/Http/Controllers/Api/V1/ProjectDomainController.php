<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProjectDomainRequest;
use App\Http\Resources\Api\V1\ProjectDomainResource;
use App\Models\ProjectDomain;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectDomainController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ProjectDomain::query()->with(['project', 'integrationSyncStates']);

        if ($projectId = $request->query('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('domain_name', 'like', '%'.$search.'%')
                    ->orWhere('autodns_id', $search);
            });
        }

        return ProjectDomainResource::collection($query->orderBy('domain_name')->paginate($request->integer('per_page', 25)));
    }

    public function show(ProjectDomain $project_domain): ProjectDomainResource
    {
        $project_domain->load(['project', 'integrationSyncStates']);

        return new ProjectDomainResource($project_domain);
    }

    public function update(UpdateProjectDomainRequest $request, ProjectDomain $project_domain): ProjectDomainResource
    {
        $project_domain->update($request->validated());
        $project_domain->load(['project', 'integrationSyncStates']);

        return new ProjectDomainResource($project_domain);
    }
}
