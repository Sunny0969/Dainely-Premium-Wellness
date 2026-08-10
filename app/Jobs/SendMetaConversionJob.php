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
 * Phase 2 §11 — Meta Conversions API (server-side).
 */
class SendMetaConversionJob implements ShouldQueue
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
        $pixelId = trim((string) config('analytics.meta.pixel_id'));
        $token = trim((string) config('analytics.meta.access_token'));

        if ($pixelId === '' || $token === '') {
            return;
        }

        $version = config('analytics.meta.api_version', 'v21.0');
        $url = "https://graph.facebook.com/{$version}/{$pixelId}/events";

        $event = [
            'event_name' => $this->mapEventName($this->eventName),
            'event_time' => time(),
            'action_source' => 'website',
            'event_source_url' => $this->data['url'] ?? null,
            'user_data' => array_filter([
                'client_ip_address' => $this->data['ip'] ?? null,
                'client_user_agent' => $this->data['user_agent'] ?? null,
                'external_id' => $this->sessionId
                    ? hash('sha256', $this->sessionId)
                    : null,
                'em' => isset($this->data['email'])
                    ? [hash('sha256', strtolower(trim((string) $this->data['email'])))]
                    : null,
                'country' => isset($this->data['country_code'])
                    ? [hash('sha256', strtolower((string) $this->data['country_code']))]
                    : null,
            ]),
            'custom_data' => array_filter([
                'currency' => $this->data['currency'] ?? null,
                'value' => $this->numericValue($this->data),
                'content_ids' => isset($this->data['product_id'])
                    ? [(string) $this->data['product_id']]
                    : ($this->data['content_ids'] ?? null),
                'content_name' => $this->data['title'] ?? $this->data['product_title'] ?? $this->data['slug'] ?? null,
                'content_type' => $this->data['content_type'] ?? 'product',
                'order_id' => $this->data['order_ref'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''),
        ];

        $payload = [
            'data' => [$event],
            'access_token' => $token,
        ];

        $testCode = trim((string) config('analytics.meta.test_event_code', ''));
        if ($testCode !== '') {
            $payload['test_event_code'] = $testCode;
        }

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('Meta CAPI non-success', [
                    'event' => $this->eventName,
                    'status' => $response->status(),
                    'body' => Str::limit($response->body(), 300),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('Meta CAPI request failed', [
                'event' => $this->eventName,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function mapEventName(string $name): string
    {
        return match ($name) {
            'product_view' => 'ViewContent',
            'landing_page_view' => 'ViewContent',
            'education_view' => 'ViewContent',
            'add_to_cart' => 'AddToCart',
            'begin_checkout' => 'InitiateCheckout',
            'purchase' => 'Purchase',
            'newsletter_signup' => 'Lead',
            'contact_form' => 'Contact',
            default => Str::studly($name),
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
