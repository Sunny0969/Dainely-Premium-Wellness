<?php

namespace App\Http\Middleware;

use App\Services\ShopifyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyShopifyWebhook
{
    public function __construct(protected ShopifyService $shopify) {}

    public function handle(Request $request, Closure $next): Response
    {
        $rawBody = $request->getContent();
        $hmacHeader = (string) $request->header('X-Shopify-Hmac-Sha256', '');

        if (! $this->shopify->validateWebhookSignature($rawBody, $hmacHeader)) {
            Log::warning('Shopify webhook: invalid HMAC signature', [
                'ip' => $request->ip(),
                'topic' => $request->header('X-Shopify-Topic'),
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
