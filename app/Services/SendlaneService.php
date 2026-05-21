<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendlaneService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $apiSecret;
    protected string $subdomain;

    public function __construct()
    {
        $this->apiUrl    = config('sendlane.api_url');
        $this->apiKey    = config('sendlane.api_key');
        $this->apiSecret = config('sendlane.api_secret');
        $this->subdomain = config('sendlane.subdomain');
    }

    /**
     * Subscribe a contact to a Sendlane list after purchase.
     */
    public function subscribeContact(array $contact, string $locale = 'en'): bool
    {
        $listId = config("sendlane.lists.{$locale}");
        if (!$listId) {
            Log::warning("No Sendlane list configured for locale: {$locale}");
            return false;
        }

        $payload = [
            'email'       => $contact['email'],
            'first_name'  => $contact['first_name'] ?? '',
            'last_name'   => $contact['last_name'] ?? '',
            'list_ids'    => [$listId],
            'fields'      => [
                'country' => $contact['country'] ?? '',
                'locale'  => $locale,
            ],
        ];

        return $this->post('contacts', $payload);
    }

    /**
     * Tag a contact with an order confirmation event.
     */
    public function triggerOrderConfirmation(string $email, array $orderData, string $locale = 'en'): bool
    {
        $payload = [
            'email'   => $email,
            'tag'     => "order_confirmed_{$locale}",
            'payload' => $orderData,
        ];

        return $this->post('contacts/tags', $payload);
    }

    protected function post(string $endpoint, array $data): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('Sendlane API key not configured.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->post($this->apiUrl . $endpoint, $data);

            if ($response->successful()) {
                return true;
            }

            Log::error("Sendlane API error [{$endpoint}]", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Sendlane request exception: ' . $e->getMessage());
            return false;
        }
    }
}
