<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $provider = (string) $request->route('integration', '');

        $secrets = (array) config('integrations.webhook_hmac_secrets', []);
        $bearerTokens = (array) config('integrations.webhook_bearer_tokens', []);

        $secret = $secrets[$provider] ?? null;
        $expectedBearer = $bearerTokens[$provider] ?? null;

        if (is_string($expectedBearer) && $expectedBearer !== '' && hash_equals($expectedBearer, (string) $request->bearerToken())) {
            return $next($request);
        }

        if (is_string($secret) && $secret !== '') {
            $signature = (string) $request->header('X-Webhook-Signature', '');
            if ($signature !== '' && $this->validHmac($request->getContent(), $secret, $signature)) {
                return $next($request);
            }
        }

        abort(Response::HTTP_UNAUTHORIZED, 'Invalid webhook authentication');
    }

    private function validHmac(string $payload, string $secret, string $signature): bool
    {
        $signature = preg_replace('#^sha256=#i', '', $signature) ?? $signature;
        $signature = trim($signature);
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
