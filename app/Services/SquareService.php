<?php
namespace App\Services;
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

    public function __construct()
    {
        $this->accessToken   = config('square.access_token', '');
        $this->locationId    = config('square.location_id', '');
        $this->applicationId = config('square.application_id', '');
        $this->environment   = config('square.environment', 'sandbox');
        $this->apiBase       = $this->environment === 'production'
            ? 'https://connect.squareup.com/v2'
            : 'https://connect.squareupsandbox.com/v2';
    }

    public function getApplicationId(): string { return $this->applicationId; }
    public function getEnvironment(): string   { return $this->environment; }
    public function getLocationId(): string    { return $this->locationId; }

    public function createPayment(string $sourceId, int $amountCents, string $orderRef, string $currency = 'USD'): array
    {
        if (empty($this->accessToken)) {
            Log::warning('Square: no access token — using mock payment');
            return ['success' => true, 'payment_id' => 'MOCK_' . Str::upper(Str::random(12)), 'mock' => true];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type'  => 'application/json',
                'Square-Version'=> '2024-10-17',
            ])->post($this->apiBase . '/payments', [
                'source_id'       => $sourceId,
                'idempotency_key' => Str::uuid()->toString(),
                'amount_money'    => ['amount' => $amountCents, 'currency' => $currency],
                'location_id'     => $this->locationId,
                'reference_id'    => $orderRef,
                'note'            => 'Dainely order: ' . $orderRef,
            ]);

            $body = $response->json();

            if ($response->successful() && isset($body['payment']['id'])) {
                return ['success' => true, 'payment_id' => $body['payment']['id'], 'status' => $body['payment']['status']];
            }

            Log::error('Square payment failed', ['response' => $body]);
            return ['success' => false, 'errors' => $body['errors'] ?? [['detail' => 'Payment declined. Please try a different card.']]];
        } catch (\Exception $e) {
            Log::error('Square payment exception: ' . $e->getMessage());
            return ['success' => false, 'errors' => [['detail' => 'Payment service temporarily unavailable. Please try again.']]];
        }
    }

    public function validateWebhookSignature(string $body, string $signature, string $url): bool
    {
        $sigKey = config('square.webhook_signature_key', '');
        if (empty($sigKey)) return true;
        return hash_equals(base64_encode(hash_hmac('sha256', $url . $body, $sigKey, true)), $signature);
    }

    public function refundPayment(string $paymentId, int $amountCents, string $reason = ''): array
    {
        if (empty($this->accessToken)) return ['success' => true, 'mock' => true];
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type'  => 'application/json',
                'Square-Version'=> '2024-10-17',
            ])->post($this->apiBase . '/refunds', [
                'idempotency_key' => Str::uuid()->toString(),
                'payment_id'      => $paymentId,
                'amount_money'    => ['amount' => $amountCents, 'currency' => 'USD'],
                'reason'          => $reason ?: 'Customer refund',
            ]);
            $body = $response->json();
            return ['success' => $response->successful(), 'refund_id' => $body['refund']['id'] ?? null, 'errors' => $body['errors'] ?? []];
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => [['detail' => $e->getMessage()]]];
        }
    }
}
