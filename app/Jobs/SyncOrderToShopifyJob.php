<?php

namespace App\Jobs;

use App\Services\OrderPersistenceService;
use App\Services\ShopifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOrderToShopifyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [60, 300, 900, 1800, 3600];

    public function __construct(public string $orderRef) {}

    public function handle(ShopifyService $shopify, OrderPersistenceService $persistence): void
    {
        $payload = $persistence->loadPendingSyncPayload($this->orderRef);

        if ($payload === null || ! is_array($payload['shopify_payload'] ?? null)) {
            Log::warning('SyncOrderToShopifyJob: no payload found', ['ref' => $this->orderRef]);

            return;
        }

        $result = $shopify->createOrderFromCheckout($payload['shopify_payload']);

        if ($result['success'] && ! empty($result['order'])) {
            $persistence->markShopifySynced($this->orderRef, $result['order']);
            Log::info('SyncOrderToShopifyJob: order synced', [
                'ref'  => $this->orderRef,
                'name' => $result['order']['name'] ?? null,
            ]);

            return;
        }

        $error = $result['error'] ?? 'Unknown Shopify sync error';
        $persistence->markShopifySyncFailed($this->orderRef, $error);
        Log::error('SyncOrderToShopifyJob: sync failed', [
            'ref'     => $this->orderRef,
            'error'   => $error,
            'attempt' => $this->attempts(),
        ]);

        throw new \RuntimeException($error);
    }
}
