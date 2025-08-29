<?php

namespace IRPayment\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use IRPayment\Facades\IRPayment;
use IRPayment\Models\Payment;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class PaymentZarinpalProcessTest extends TestCase
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

        IRPayment::driver('zarinpal')->process($payment);

        Http::assertSent(function (Request $request) use ($payment) {
            return
                $request['currency'] == 'IRR' &&
                $request['amount'] == $payment->amount &&
                $request['description'] == $payment->description &&
                $request['merchant_id'] == '1234';
        });
    }
}
