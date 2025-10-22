<?php

namespace IRPayment\Tests\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\MessageBag;
use IRPayment\Enums\PaymentStatus;
use IRPayment\Events\PaymentCanceled;
use IRPayment\Events\PaymentFailed;
use IRPayment\Events\PaymentVerified;
use IRPayment\Models\Payment;
use IRPayment\Tests\TestCase;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class PaypingPaymentVerifyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public function test_payment_verification_status_canceled(): void
    {
        $paymentCode = (string) fake()->randomNumber();
        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->pending()
            ->for($order, 'paymentable')
            ->state(['authority_key' => $paymentCode])
            ->create();

        $requestData = [
            'status' => '0',
            'errorCode' => 110,
            'data' => json_encode([
                'clientRefId' => $payment->id,
                'paymentCode' => $paymentCode,
                'amount' => $payment->amount,
                'gatewayAmount' => $payment->amount,

            ]),
        ];

        $response = $this->get(route('irpayment.payment.payping.verify', $requestData));

        $payment->refresh();

        $this->assertSame($payment->status, PaymentStatus::CANCELED);

        $response->assertViewIs('irpayment::canceled');
        $response->assertViewHas('payment', fn (Payment $actualPayment) => $actualPayment->is($payment));

        Http::assertNothingSent();

        Event::assertDispatched(PaymentCanceled::class);
        Event::assertNotDispatched(PaymentFailed::class);
        Event::assertNotDispatched(PaymentVerified::class);
    }
}
