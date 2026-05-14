<?php

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\CostLineItemController;
use App\Http\Controllers\Api\V1\IntegrationSyncStateController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ProjectDomainController;
use App\Http\Controllers\Api\V1\StoreIntegrationWebhookController;
use Illuminate\Support\Facades\Route;

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

        Route::get('cost-line-items', [CostLineItemController::class, 'index']);
        Route::get('cost-line-items/{cost_line_item}', [CostLineItemController::class, 'show']);
        Route::patch('cost-line-items/{cost_line_item}', [CostLineItemController::class, 'update']);

        Route::get('integration-sync-states', [IntegrationSyncStateController::class, 'index']);
        Route::post('integration-sync-states', [IntegrationSyncStateController::class, 'store']);
        Route::patch('integration-sync-states/{integration_sync_state}', [IntegrationSyncStateController::class, 'update']);
    });
});
