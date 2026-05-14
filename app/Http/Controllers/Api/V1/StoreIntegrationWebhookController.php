<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\IntegrationProvider;
use App\Http\Controllers\Controller;
use App\Services\Integrations\IntegrationWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StoreIntegrationWebhookController extends Controller
{
    public function __invoke(Request $request, string $integration, IntegrationWebhookService $webhooks): JsonResponse
    {
        $provider = IntegrationProvider::from($integration);
        $payload = $request->all();
        $dedupeKey = $request->header('X-Webhook-Dedupe') ?? Arr::get($payload, 'event_id') ?? Arr::get($payload, 'dedupe_key');

        $result = $webhooks->recordEvent($provider, $payload, is_string($dedupeKey) ? $dedupeKey : null, $request->ip());

        if (! $result['is_duplicate'] && $result['event']->processed_at === null) {
            $webhooks->handleInbound($provider, $payload);
            $result['event']->update(['processed_at' => now()]);
        }

        return response()->json([
            'accepted' => true,
            'duplicate' => $result['is_duplicate'],
        ]);
    }
}
