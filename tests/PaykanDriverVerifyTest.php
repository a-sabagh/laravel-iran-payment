<?php

namespace IRPayment\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use IRPayment\DTO\VerificationValueObject;
use IRPayment\Facades\IRPayment;
use IRPayment\Models\Payment;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

/** @see \IRPayment\Drivers\Paykan */
class PaykanDriverVerifyTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public function test_paykan_verify_success(): void
    {
        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->for($order, 'paymentable')
            ->create();

        $merchantId = fake()->numerify('merchant-####');

        $this->app->config->set('irpayment.drivers.paykan', [
            'merchant_id' => $merchantId,
            'currency' => 'IRR',
        ]);

        Http::fake([
            'https://pgw.paykan.ir/api/v1/withdraw/verify/' => Http::response([
                'data' => [
                    'status' => 'CONFIRMED',
                    'card_no' => '6037991234567890',
                    'hashed_card_no' => '****-****-****-7890',
                    'ref_num' => '1000005489', // Paykan return string
                ],
            ], 200),
        ]);

        $responseVO = IRPayment::driver('paykan')->verify($payment->amount, [
            'order_id' => $order->id,
            'tracking_code' => 'paykan-token-123',
            'ref_num' => '1000005489',
        ]);

        $this->assertInstanceOf(VerificationValueObject::class, $responseVO);
        // code can be fetched from string
        $this->assertSame(200, $responseVO->code);
        $this->assertNotNull($responseVO->message);
        $this->assertSame('6037991234567890', $responseVO->cardHash);
        $this->assertSame('****-****-****-7890', $responseVO->cardMask);
        // also casting reference id into number
        $this->assertSame(1000005489, $responseVO->referenceId);

        Http::assertSent(function (Request $request) use ($merchantId, $payment, $order) {
            return $request->url() === 'https://pgw.paykan.ir/api/v1/withdraw/verify/'
                && $request['merchant_id'] === $merchantId
                && $request['amount'] === $payment->amount
                && $request['order_id'] === $order->id
                && $request['tracking_code'] === 'paykan-token-123'
                && $request['ref_num'] === '1000005489';
        });

        Http::assertSentCount(1);
    }

    public function test_paykan_verify_failed(): void
    {
        $this->app->setLocale('fa_IR');

        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->for($order, 'paymentable')
            ->create();

        $merchantId = fake()->numerify('merchant-####');

        $this->app->config->set('irpayment.drivers.paykan', [
            'merchant_id' => $merchantId,
            'currency' => 'IRR',
        ]);

        Http::fake([
            'https://pgw.paykan.ir/api/v1/withdraw/verify/' => Http::response([
                'data' => [
                    'status' => 'FAILED',
                ],
            ], 200),
        ]);

        $responseVO = IRPayment::driver('paykan')->verify($payment->amount, [
            'order_id' => $order->id,
            'tracking_code' => 'paykan-token-123',
            'ref_num' => '1000005489',
        ]);

        $this->assertInstanceOf(VerificationValueObject::class, $responseVO);
        $this->assertSame(508, $responseVO->code);
        $this->assertNotNull($responseVO->message);
        $this->assertSame('تراکنش ناموفق', $responseVO->message);
    }
}
