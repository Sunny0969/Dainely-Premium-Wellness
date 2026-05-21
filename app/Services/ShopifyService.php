<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    protected string $domain;
    protected string $token;
    protected string $apiVersion;
    protected string $apiBase;

    public function __construct()
    {
        $this->domain     = config('shopify.store_domain', 'dmede-usa.myshopify.com');
        $this->token      = config('shopify.access_token', '');
        $this->apiVersion = config('shopify.api_version', '2024-10');
        $this->apiBase    = "https://{$this->domain}/admin/api/{$this->apiVersion}";
    }

    protected function headers(): array
    {
        return [
            'X-Shopify-Access-Token' => $this->token,
            'Content-Type'           => 'application/json',
        ];
    }

    /**
     * Create a product in Shopify.
     */
    public function createProduct(array $data): ?array
    {
        if (empty($this->token)) {
            Log::info('Shopify: no token — mock product', ['title' => $data['title']]);
            return ['id' => rand(1000000000, 9999999999), 'title' => $data['title']];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->post($this->apiBase . '/products.json', ['product' => $data]);

            if ($response->successful()) {
                return $response->json()['product'] ?? null;
            }
            Log::error('Shopify createProduct failed', ['body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Shopify createProduct exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * List all products.
     */
    public function getProducts(int $limit = 50): array
    {
        if (empty($this->token)) return [];
        try {
            $response = Http::withHeaders($this->headers())
                ->get($this->apiBase . '/products.json', ['limit' => $limit]);
            return $response->successful() ? ($response->json()['products'] ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Create an order in Shopify (called from queue job).
     */
    public function createOrder(array $data): ?array
    {
        if (empty($this->token)) {
            Log::info('Shopify: no token — mock order', ['ref' => $data['order_number'] ?? '']);
            return ['id' => rand(1000000, 9999999), 'order_number' => 'MOCK-' . rand(1000,9999)];
        }

        $lineItems = array_map(fn($item) => [
            'title'      => $item['product_name'],
            'quantity'   => $item['quantity'],
            'price'      => number_format($item['unit_price_usd'], 2, '.', ''),
            'sku'        => $item['sku'] ?? '',
            'requires_shipping' => true,
        ], $data['items']);

        $payload = [
            'order' => [
                'line_items'       => $lineItems,
                'email'            => $data['customer_email'],
                'financial_status' => 'paid',
                'tags'             => 'dainely-platform,' . ($data['locale'] ?? 'en'),
                'note'             => 'Dainely Order: ' . ($data['order_number'] ?? ''),
                'note_attributes'  => [
                    ['name' => 'dainely_order_number', 'value' => $data['order_number'] ?? ''],
                    ['name' => 'square_payment_id',    'value' => $data['square_payment_id'] ?? ''],
                ],
                'shipping_address' => [
                    'first_name' => $data['customer_first_name'],
                    'last_name'  => $data['customer_last_name'],
                    'address1'   => $data['shipping_address1'],
                    'address2'   => $data['shipping_address2'] ?? '',
                    'city'       => $data['shipping_city'],
                    'province'   => $data['shipping_state'] ?? '',
                    'zip'        => $data['shipping_zip'],
                    'country'    => $data['shipping_country'],
                    'phone'      => $data['customer_phone'] ?? '',
                ],
                'send_receipt'             => true,
                'send_fulfillment_receipt' => true,
            ],
        ];

        if (!empty($data['discount_code'])) {
            $payload['order']['discount_codes'] = [[
                'code'   => $data['discount_code'],
                'amount' => number_format($data['discount_amount'] ?? 0, 2),
                'type'   => 'fixed_amount',
            ]];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->post($this->apiBase . '/orders.json', $payload);

            if ($response->successful()) {
                return $response->json()['order'] ?? null;
            }
            Log::error('Shopify createOrder failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Shopify createOrder exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate Shopify webhook HMAC.
     */
    public function validateWebhookSignature(string $body, string $hmacHeader): bool
    {
        $secret = config('shopify.webhook_secret', '');
        if (empty($secret)) return true;
        return hash_equals(
            base64_encode(hash_hmac('sha256', $body, $secret, true)),
            $hmacHeader
        );
    }
}
