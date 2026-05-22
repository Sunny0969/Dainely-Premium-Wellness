<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShopifyAuthController extends Controller
{
    /**
     * Start OAuth — install DMEDE_StoreAdminApp on the store and get shpat_ token.
     */
    public function install(Request $request): RedirectResponse
    {
        $clientId = config('shopify.client_id');
        $shop = $this->normalizeShopDomain($request->query('shop', config('shopify.store_domain')));

        if (empty($clientId)) {
            return redirect()->route('shop.index')
                ->with('error', 'SHOPIFY_CLIENT_ID is missing in .env.');
        }

        $state = Str::random(40);
        $request->session()->put('shopify_oauth_state', $state);
        $request->session()->put('shopify_oauth_shop', $shop);

        $query = http_build_query([
            'client_id'    => $clientId,
            'scope'        => config('shopify.scopes'),
            'redirect_uri' => config('shopify.redirect_uri'),
            'state'        => $state,
        ]);

        return redirect("https://{$shop}/admin/oauth/authorize?{$query}");
    }

    /**
     * OAuth callback — exchange code for Admin API access token (shpat_...).
     */
    public function callback(Request $request): View|RedirectResponse
    {
        $sessionState = $request->session()->pull('shopify_oauth_state');
        $shop = $request->session()->pull('shopify_oauth_shop')
            ?? config('shopify.store_domain');

        if (! $request->has('code')) {
            return redirect()->route('shopify.install')
                ->with('error', 'Authorization was cancelled or denied.');
        }

        if (! $sessionState || ! hash_equals($sessionState, (string) $request->query('state', ''))) {
            return redirect()->route('shopify.install')
                ->with('error', 'Invalid OAuth state. Please try again.');
        }

        $shop = $this->normalizeShopDomain($shop);

        $http = Http::asForm();
        if (! config('shopify.verify_ssl', true)) {
            $http = $http->withoutVerifying();
        }

        $response = $http->post("https://{$shop}/admin/oauth/access_token", [
            'client_id'     => config('shopify.client_id'),
            'client_secret' => config('shopify.client_secret'),
            'code'          => $request->query('code'),
        ]);

        if (! $response->successful()) {
            return view('shopify.auth-result', [
                'success' => false,
                'message' => 'Token exchange failed: ' . $response->body(),
                'shop'    => $shop,
            ]);
        }

        $accessToken = trim((string) ($response->json('access_token') ?? ''));

        if ($accessToken === '') {
            return view('shopify.auth-result', [
                'success' => false,
                'message' => 'No access_token in Shopify response.',
                'shop'    => $shop,
            ]);
        }

        file_put_contents(storage_path('app/shopify_access_token'), $accessToken . PHP_EOL);

        return view('shopify.auth-result', [
            'success'      => true,
            'message'      => 'Shopify connected successfully. Products will load on the homepage.',
            'shop'         => $shop,
            'token_prefix' => substr($accessToken, 0, 6) . '…',
        ]);
    }

    protected function normalizeShopDomain(string $shop): string
    {
        $shop = strtolower(trim($shop));
        $shop = preg_replace('#^https?://#', '', $shop);
        $shop = rtrim($shop, '/');

        if (! str_contains($shop, '.')) {
            $shop .= '.myshopify.com';
        }

        return $shop;
    }
}
