<?php

namespace IRPayment\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;
use IRPayment\DTO\ProcessResponseValueObject;
use IRPayment\DTO\VerificationValueObject;
use IRPayment\Exceptions\PaymentDriverException;
use IRPayment\Facades\IRPayment;
use IRPayment\Models\Payment;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class PaymentZarinpalPublicActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public function test_zarinpal_process_payment_with_payment_object(): void
    {
        $requestResponse = file_get_contents(__DIR__.'/fake/zarinpal/request.json');

        Http::fake([
            'https://api.zarinpal.com/pg/v4/payment/request.json' => Http::response($requestResponse, 200),
        ]);

        $this->app->config->set('irpayment.drivers.zarinpal', [
            'merchant_id' => '1234',
            'currency' => 'IRR',
        ]);

        $order = Order::factory()->create();

        $payment = Payment::factory()->for(
            $order, 'paymentable'
        )->create();

        $processResponseVO = IRPayment::driver('zarinpal')->process($payment);

        $this->assertInstanceOf(ProcessResponseValueObject::class, $processResponseVO);
        $this->assertStringEndsWith($processResponseVO->authorityKey, 'A0000000000000000000000000000wwOGYpd');
        $this->assertStringEndsWith('A0000000000000000000000000000wwOGYpd', $processResponseVO->redirectResponseUrl);

        Http::assertSent(function (Request $request) use ($payment) {
            return
                $request['currency'] == 'IRR' &&
                $request['amount'] == $payment->amount &&
                $request['description'] == $payment->description &&
                $request['merchant_id'] == '1234';
        });
    }

    public function test_zarinpal_process_payment_with_payment_object_failed(): void
    {
        $this->expectException(PaymentDriverException::class);
        $this->expectExceptionCode(-10);
        $this->expectExceptionMessage(Lang::get('irpayment::messages.zarinpal.-10'));
        $this->expectExceptionMessage('Terminal is not valid, please check merchant_id or ip address.');

        $requestResponseFailed = file_get_contents(__DIR__.'/fake/zarinpal/request-10.json');

        Http::fake([
            'https://api.zarinpal.com/pg/v4/payment/request.json' => Http::response($requestResponseFailed, 200),
        ]);

        $order = Order::factory()->create();

        $payment = Payment::factory()->for(
            $order, 'paymentable'
        )->create();

        IRPayment::driver('zarinpal')->process($payment);
    }
}
