<?php

namespace Tests\Unit;

use App\Services\SquareService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SquareServiceTest extends TestCase
{
    public function test_create_payment_always_uses_configured_charge_currency(): void
    {
        config([
            'square.access_token'   => 'test-token',
            'square.location_id'    => 'TESTLOC',
            'square.environment'    => 'sandbox',
            'square.charge_currency'=> 'USD',
            'square.verify_ssl'     => false,
        ]);

        Http::fake([
            'https://connect.squareupsandbox.com/v2/payments' => Http::response([
                'payment' => ['id' => 'pay_test', 'status' => 'COMPLETED'],
            ], 200),
        ]);

        $service = new SquareService;
        $result  = $service->createPayment('cnon:test', 5999, 'DLY-TEST', 'EUR');

        $this->assertTrue($result['success']);
        $this->assertSame('pay_test', $result['payment_id']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://connect.squareupsandbox.com/v2/payments'
                && ($body['amount_money']['currency'] ?? null) === 'USD'
                && ($body['amount_money']['amount'] ?? null) === 5999;
        });
    }

    public function test_get_charge_currency_defaults_to_usd(): void
    {
        config(['square.charge_currency' => 'USD']);

        $this->assertSame('USD', (new SquareService)->getChargeCurrency());
    }
}
