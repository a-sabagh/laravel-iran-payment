<?php

namespace IRPayment\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use IRPayment\DTO\ProcessResponseValueObject;
use IRPayment\Facades\IRPayment;
use IRPayment\Models\Payment;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class PaymentPaypingProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public function test_paping_driver_process_payment(): void
    {
        // create payment
        $order = Order::factory()->create();
        $payment = Payment::factory()
            ->for($order, 'paymentable')
            ->create();
        // configure payping gateway
        $token = fake()->asciify('payping-user-************');
        $this->app->config->set('irpayment.drivers.payping', [
            'token' => $token,
            'currency' => 'IRR',
        ]);
        // mock pay request
        $requestResponse = file_get_contents(__DIR__.'/fake/paping/pay-200.json');

        Http::fake([
            'https://api.payping.ir/v3/pay' => Http::response($requestResponse, 200),
        ]);

        $responseVO = IRPayment::driver('payping')
            ->process($payment);

        $this->assertInstanceOf(ProcessResponseValueObject::class, $responseVO);
        $this->assertStringEndsWith('155', $responseVO->redirectResponseUrl);
        $this->assertSame('155', $responseVO->authorityKey);
        // http request test
        Http::assertSent(function (Request $request) use ($token, $payment) {
            return $request->hasHeader('Authorization', "Bearer {$token}")
                && $request['amount'] == $payment->amount
                && $request['payerIdentity'] == $payment->phone
                && $request['description'] == $payment->description
                && $request['clientRefId'] == $payment->id
                && ! empty($request['returnUrl']);
        });
        Http::assertSentCount(1);
    }
}
