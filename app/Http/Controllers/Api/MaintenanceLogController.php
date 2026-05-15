<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMaintenanceLogRequest;
use App\Http\Resources\Api\MaintenanceLogResource;
use App\Models\MaintenanceHistory;
use Illuminate\Http\JsonResponse;

class MaintenanceLogController extends Controller
{
    public function store(StoreMaintenanceLogRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (! array_key_exists('has_errors', $data)) {
            $data['has_errors'] = false;
        }

        $history = MaintenanceHistory::query()->forceCreate($data);
        $history->load('project');

        return (new MaintenanceLogResource($history))
            ->response()
            ->setStatusCode(201);
    }
}
