<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateCostLineItemRequest;
use App\Http\Resources\Api\V1\CostLineItemResource;
use App\Models\CostLineItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CostLineItemController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CostLineItem::query()->with(['client', 'project', 'integrationSyncStates']);

        if ($clientId = $request->query('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($projectId = $request->query('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($status = $request->query('moco_sync_status')) {
            $query->where('moco_sync_status', $status);
        }

        return CostLineItemResource::collection($query->orderByDesc('id')->paginate($request->integer('per_page', 25)));
    }

    public function show(CostLineItem $costLineItem): CostLineItemResource
    {
        $costLineItem->load(['client', 'project', 'integrationSyncStates']);

        return new CostLineItemResource($costLineItem);
    }

    public function update(UpdateCostLineItemRequest $request, CostLineItem $costLineItem): CostLineItemResource
    {
        $costLineItem->update($request->validated());
        $costLineItem->load(['client', 'project', 'integrationSyncStates']);

        return new CostLineItemResource($costLineItem);
    }
}
