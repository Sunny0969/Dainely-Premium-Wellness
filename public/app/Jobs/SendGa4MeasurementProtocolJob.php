<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Phase 2 §11 — GA4 Measurement Protocol (server-side).
 */
class SendGa4MeasurementProtocolJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $eventName,
        public array $data,
        public ?string $sessionId = null,
    ) {}

    public function handle(): void
    {
        $measurementId = trim((string) config('analytics.ga4.measurement_id'));
        $apiSecret = trim((string) config('analytics.ga4.api_secret'));

        if ($measurementId === '' || $apiSecret === '') {
            return;
        }

        $clientId = $this->clientId();
        $ga4Name = $this->mapEventName($this->eventName);

        $payload = [
            'client_id' => $clientId,
            'events' => [[
                'name' => $ga4Name,
                'params' => array_filter([
                    'engagement_time_msec' => 1,
                    'session_id' => substr((string) ($this->sessionId ?? ''), 0, 36),
                    'locale' => $this->data['locale'] ?? null,
                    'country' => $this->data['country_code'] ?? null,
                    'currency' => $this->data['currency'] ?? null,
                    'value' => $this->numericValue($this->data),
                    'items' => $this->data['items'] ?? null,
                    'transaction_id' => $this->data['order_ref'] ?? $this->data['transaction_id'] ?? null,
                    'item_id' => $this->data['product_id'] ?? $this->data['item_id'] ?? null,
                    'item_name' => $this->data['title'] ?? $this->data['product_title'] ?? null,
                    'page_location' => $this->data['url'] ?? null,
                    'page_path' => $this->data['path'] ?? null,
                ], fn ($v) => $v !== null && $v !== ''),
            ]],
        ];

        $endpoint = rtrim((string) config('analytics.ga4.endpoint'), '?');
        $url = $endpoint.'?'.http_build_query([
            'measurement_id' => $measurementId,
            'api_secret' => $apiSecret,
        ]);

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('GA4 MP non-success', [
                    'event' => $this->eventName,
                    'status' => $response->status(),
                    'body' => Str::limit($response->body(), 300),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('GA4 MP request failed', [
                'event' => $this->eventName,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function clientId(): string
    {
        $seed = $this->sessionId ?: ($this->data['client_id'] ?? Str::uuid()->toString());

        return substr(hash('sha256', (string) $seed), 0, 16).'.'.substr(hash('sha256', (string) $seed.'x'), 0, 10);
    }

    protected function mapEventName(string $name): string
    {
        return match ($name) {
            'product_view' => 'view_item',
            'landing_page_view' => 'view_item',
            'education_view' => 'view_content',
            'add_to_cart' => 'add_to_cart',
            'begin_checkout' => 'begin_checkout',
            'purchase' => 'purchase',
            'newsletter_signup' => 'sign_up',
            'contact_form' => 'generate_lead',
            default => Str::snake($name),
        };
    }

    protected function numericValue(array $data): ?float
    {
        if (isset($data['value'])) {
            return (float) $data['value'];
        }
        if (isset($data['amount_cents'])) {
            return round(((int) $data['amount_cents']) / 100, 2);
        }
        if (isset($data['price'])) {
            return (float) $data['price'];
        }

        return null;
    }
}
