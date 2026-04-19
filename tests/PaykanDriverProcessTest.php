<?php

namespace IRPayment\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use IRPayment\DTO\ProcessResponseValueObject;
use IRPayment\Exceptions\PaymentDriverException;
use IRPayment\Facades\IRPayment;
use IRPayment\Models\Payment;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class PaykanDriverProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public function test_paykan_driver_process_payment(): void
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
            'https://pgw.paykan.ir/api/v1/withdraw/' => Http::response([
                'token' => 'paykan-token-123',
                'ref_num' => 'paykan-ref-456',
            ], 200),
        ]);

        $responseVO = IRPayment::driver('paykan')->process($payment);

        $this->assertInstanceOf(ProcessResponseValueObject::class, $responseVO);

        $this->assertSame('https://pgw.paykan.ir/pgw/pay/paykan-token-123', $responseVO->redirectResponseUrl);
        $this->assertSame('paykan-ref-456', $responseVO->authorityKey);

        Http::assertSent(function (Request $request) use ($merchantId, $payment, $order) {
            return $request->url() === 'https://pgw.paykan.ir/api/v1/withdraw/'
                && $request['merchant_id'] === $merchantId
                && $request['amount'] === $payment->amount
                && $request['order_id'] === $order->id
                && $request['callback_method'] === 'GET'
                && ! empty($request['callback_url']);
        });

        Http::assertSentCount(1);
    }

    public function test_paykan_driver_process_return_bad_request(): void
    {
        $this->app->setLocale('fa_IR');

        $this->expectException(PaymentDriverException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('مشکلی در ارسال درخواست پیش آمده است');

        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->for($order, 'paymentable')
            ->create();

        $this->app->config->set('irpayment.drivers.paykan', [
            'merchant_id' => fake()->numerify('merchant-####'),
            'currency' => 'IRR',
        ]);

        Http::fake([
            'https://pgw.paykan.ir/api/v1/withdraw/' => Http::response([], 400),
        ]);

        IRPayment::driver('paykan')->process($payment);
    }
}
