<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SquareService
{
    protected string $accessToken;

    protected string $locationId;

    protected string $applicationId;

    protected string $environment;

    protected string $apiBase;

    protected bool $verifySsl;

    public function __construct()
    {
        $this->accessToken   = (string) config('square.access_token', '');
        $this->locationId    = (string) config('square.location_id', '');
        $this->applicationId = (string) config('square.application_id', '');
        $this->environment   = (string) config('square.environment', 'sandbox');
        $this->verifySsl     = (bool) config('square.verify_ssl', true);
        $this->apiBase       = $this->environment === 'production'
            ? 'https://connect.squareup.com/v2'
            : 'https://connect.squareupsandbox.com/v2';
    }

    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getLocationId(): string
    {
        if ($this->locationId !== '') {
            return $this->locationId;
        }

        return $this->resolveLocationId();
    }

    public function isConfigured(): bool
    {
        return $this->applicationId !== ''
            && $this->accessToken !== ''
            && $this->getLocationId() !== '';
    }

    public function createPayment(string $sourceId, int $amountCents, string $orderRef, string $currency = 'USD'): array
    {
        if (empty($this->accessToken)) {
            Log::warning('Square: no access token — using mock payment');

            return ['success' => true, 'payment_id' => 'MOCK_' . Str::upper(Str::random(12)), 'mock' => true];
        }

        $locationId = $this->getLocationId();
        if ($locationId === '') {
            return [
                'success' => false,
                'errors'  => [['detail' => 'Square Location ID is not configured. Add SQUARE_LOCATION_ID to .env.']],
            ];
        }

        try {
            $response = $this->httpClient()->post($this->apiBase . '/payments', [
                'source_id'       => $sourceId,
                'idempotency_key' => Str::uuid()->toString(),
                'amount_money'    => ['amount' => $amountCents, 'currency' => $currency],
                'location_id'     => $locationId,
                'reference_id'    => $orderRef,
                'note'            => 'Dainely order: ' . $orderRef,
            ]);

            $body = $response->json();

            if ($response->successful() && isset($body['payment']['id'])) {
                return [
                    'success'    => true,
                    'payment_id' => $body['payment']['id'],
                    'status'     => $body['payment']['status'],
                ];
            }

            Log::error('Square payment failed', ['response' => $body]);

            return [
                'success' => false,
                'errors'  => $body['errors'] ?? [['detail' => 'Payment declined. Please try a different card.']],
            ];
        } catch (\Exception $e) {
            Log::error('Square payment exception: ' . $e->getMessage());

            return [
                'success' => false,
                'errors'  => [['detail' => 'Payment service temporarily unavailable. Please try again.']],
            ];
        }
    }

    public function validateWebhookSignature(string $body, string $signature, string $url): bool
    {
        $sigKey = config('square.webhook_signature_key', '');
        if (empty($sigKey)) {
            return true;
        }

        return hash_equals(
            base64_encode(hash_hmac('sha256', $url . $body, $sigKey, true)),
            $signature
        );
    }

    public function refundPayment(string $paymentId, int $amountCents, string $reason = ''): array
    {
        if (empty($this->accessToken)) {
            return ['success' => true, 'mock' => true];
        }

        try {
            $response = $this->httpClient()->post($this->apiBase . '/refunds', [
                'idempotency_key' => Str::uuid()->toString(),
                'payment_id'      => $paymentId,
                'amount_money'    => ['amount' => $amountCents, 'currency' => 'USD'],
                'reason'          => $reason ?: 'Customer refund',
            ]);
            $body = $response->json();

            return [
                'success'   => $response->successful(),
                'refund_id' => $body['refund']['id'] ?? null,
                'errors'    => $body['errors'] ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => [['detail' => $e->getMessage()]]];
        }
    }

    protected function resolveLocationId(): string
    {
        if ($this->accessToken === '') {
            return '';
        }

        try {
            $response = $this->httpClient()->get($this->apiBase . '/locations');
            if (! $response->successful()) {
                Log::warning('Square: could not fetch locations', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return '';
            }

            $locations = $response->json('locations') ?? [];
            foreach ($locations as $location) {
                if (($location['status'] ?? '') === 'ACTIVE' && ! empty($location['id'])) {
                    $this->locationId = (string) $location['id'];

                    return $this->locationId;
                }
            }

            if (! empty($locations[0]['id'])) {
                $this->locationId = (string) $locations[0]['id'];

                return $this->locationId;
            }
        } catch (\Exception $e) {
            Log::warning('Square: location lookup failed — ' . $e->getMessage());
        }

        return '';
    }

    protected function httpClient(): PendingRequest
    {
        $client = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type'  => 'application/json',
            'Square-Version'=> '2024-10-17',
        ]);

        if (! $this->verifySsl) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }
}
