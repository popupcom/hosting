<?php

use App\Http\Controllers\Api\MaintenanceLogController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\IntegrationSyncStateController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ProjectDomainController;
use App\Http\Controllers\Api\V1\ProjectServiceController;
use App\Http\Controllers\Api\V1\StoreIntegrationWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'abilities:integrations', 'throttle:api'])->group(function (): void {
    Route::get('projects', [ProjectController::class, 'index']);
    Route::post('maintenance-logs', [MaintenanceLogController::class, 'store']);
});

Route::prefix('v1')->group(function (): void {
    Route::post('webhooks/{integration}', StoreIntegrationWebhookController::class)
        ->middleware(['webhook', 'throttle:webhooks'])
        ->whereIn('integration', ['moco', 'managewp', 'autodns']);

    Route::middleware(['auth:sanctum', 'abilities:integrations', 'throttle:api'])->group(function (): void {
        Route::get('me', MeController::class);

        Route::get('clients', [ClientController::class, 'index']);
        Route::get('clients/{client}', [ClientController::class, 'show']);
        Route::patch('clients/{client}', [ClientController::class, 'update']);

        Route::get('projects', [ProjectController::class, 'index']);
        Route::get('projects/{project}', [ProjectController::class, 'show']);
        Route::patch('projects/{project}', [ProjectController::class, 'update']);

        Route::get('project-domains', [ProjectDomainController::class, 'index']);
        Route::get('project-domains/{project_domain}', [ProjectDomainController::class, 'show']);
        Route::patch('project-domains/{project_domain}', [ProjectDomainController::class, 'update']);

        Route::get('project-services', [ProjectServiceController::class, 'index']);
        Route::get('project-services/{project_service}', [ProjectServiceController::class, 'show']);
        Route::patch('project-services/{project_service}', [ProjectServiceController::class, 'update']);

        Route::get('integration-sync-states', [IntegrationSyncStateController::class, 'index']);
        Route::post('integration-sync-states', [IntegrationSyncStateController::class, 'store']);
        Route::patch('integration-sync-states/{integration_sync_state}', [IntegrationSyncStateController::class, 'update']);
    });
});
