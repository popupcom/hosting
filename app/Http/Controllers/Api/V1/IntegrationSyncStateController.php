<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreIntegrationSyncStateRequest;
use App\Http\Requests\Api\V1\UpdateIntegrationSyncStateRequest;
use App\Http\Resources\Api\V1\IntegrationSyncStateResource;
use App\Models\IntegrationSyncState;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IntegrationSyncStateController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = IntegrationSyncState::query()->with('syncable');

        if ($provider = $request->query('provider')) {
            $query->where('provider', $provider);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->query('syncable_type')) {
            $query->where('syncable_type', $type);
        }

        if ($externalId = $request->query('external_id')) {
            $query->where('external_id', $externalId);
        }

        return IntegrationSyncStateResource::collection(
            $query->orderByDesc('updated_at')->paginate($request->integer('per_page', 50)),
        );
    }

    public function store(StoreIntegrationSyncStateRequest $request): IntegrationSyncStateResource
    {
        $validated = $request->validated();
        $class = $validated['syncable_type'];
        $syncable = $class::query()->findOrFail($validated['syncable_id']);
        $payload = collect($validated)->except(['syncable_type', 'syncable_id'])->all();

        $state = $syncable->integrationSyncStates()->updateOrCreate(
            ['provider' => $payload['provider']],
            collect($payload)->except(['provider'])->all(),
        );
        $state->load('syncable');

        return new IntegrationSyncStateResource($state);
    }

    public function update(UpdateIntegrationSyncStateRequest $request, IntegrationSyncState $integrationSyncState): IntegrationSyncStateResource
    {
        $integrationSyncState->update($request->validated());
        $integrationSyncState->load('syncable');

        return new IntegrationSyncStateResource($integrationSyncState);
    }
}
