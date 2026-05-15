<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProjectServiceMocoSyncStatus;
use App\Enums\ProjectServiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProjectServiceRequest;
use App\Http\Resources\Api\V1\ProjectServiceResource;
use App\Models\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectServiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ProjectService::query()
            ->with(['project.client', 'serviceCatalogItem']);

        if ($projectId = $request->query('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($clientId = $request->query('client_id')) {
            $query->whereHas('project', fn ($q) => $q->where('client_id', $clientId));
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($mocoStatus = $request->query('moco_sync_status')) {
            $query->where('moco_sync_status', $mocoStatus);
        }

        if ($request->boolean('moco_ready')) {
            $query->where('moco_sync_status', ProjectServiceMocoSyncStatus::Ready);
        }

        if ($request->boolean('active_only', true)) {
            $query->where('status', ProjectServiceStatus::Active);
        }

        return ProjectServiceResource::collection(
            $query->orderByDesc('id')->paginate($request->integer('per_page', 25)),
        );
    }

    public function show(ProjectService $projectService): ProjectServiceResource
    {
        $projectService->load(['project.client', 'serviceCatalogItem', 'billingGroup']);

        return new ProjectServiceResource($projectService);
    }

    public function update(UpdateProjectServiceRequest $request, ProjectService $projectService): ProjectServiceResource
    {
        $projectService->update($request->validated());
        $projectService->load(['project.client', 'serviceCatalogItem', 'billingGroup']);

        return new ProjectServiceResource($projectService);
    }
}
