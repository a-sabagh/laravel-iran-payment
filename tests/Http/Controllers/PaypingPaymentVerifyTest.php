<?php

namespace IRPayment\Tests\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\MessageBag;
use IRPayment\DTO\VerificationValueObject;
use IRPayment\Enums\PaymentStatus;
use IRPayment\Events\PaymentCanceled;
use IRPayment\Events\PaymentFailed;
use IRPayment\Events\PaymentVerified;
use IRPayment\Models\Payment;
use IRPayment\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
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

    public function test_payment_verification_status_invalid(): void
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
                'clientRefId' => fake()->numberBetween(100, 1000),
                'paymentCode' => $paymentCode,
                'amount' => $payment->amount,
                'gatewayAmount' => $payment->amount,

            ]),
        ];

        $response = $this->get(route('irpayment.payment.payping.verify', $requestData));

        $this->assertSame($payment->status, PaymentStatus::PENDING);

        $response->assertViewIs('irpayment::invalid');
        $response->assertViewHas(
            'errors',
            fn (MessageBag $errors) => $errors->has('data.clientRefId')
                && $errors->missing('data.paymentCode')
                && $errors->missing('status')
        );

        Http::assertNothingSent();

        Event::assertNotDispatched(PaymentCanceled::class);
        Event::assertNotDispatched(PaymentFailed::class);
        Event::assertNotDispatched(PaymentVerified::class);
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

    public function test_payment_verification_status_already_complete(): void
    {
        $paymentCode = (string) fake()->randomNumber();
        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->complete()
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

        $this->assertSame($payment->status, PaymentStatus::COMPLETE);

        $response->assertViewIs('irpayment::invalid');
        $response->assertViewHas('errors', fn (MessageBag $errors) => $errors->has('payment'));

        Http::assertNothingSent();

        Event::assertNotDispatched(PaymentCanceled::class);
        Event::assertNotDispatched(PaymentFailed::class);
        Event::assertNotDispatched(PaymentVerified::class);
    }

    public function test_payment_verification_status_verification_failed(): void
    {
        $paymentCode = (string) fake()->randomNumber();
        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->pending()
            ->for($order, 'paymentable')
            ->state(['authority_key' => $paymentCode])
            ->create();

        $requestData = [
            'status' => '1',
            'errorCode' => 110,
            'data' => json_encode([
                'clientRefId' => $payment->id,
                'paymentCode' => $paymentCode,
                'amount' => $payment->amount,
                'gatewayAmount' => $payment->amount,

            ]),
        ];

        // mock verify request
        $requestResponse = file_get_contents(workbench_path('mock/payping/verify-400.json'));

        Http::fake([
            'https://api.zarinpal.com/pg/v4/payment/verify.json' => Http::response($requestResponse, 400),
        ]);

        $response = $this->get(route('irpayment.payment.payping.verify', $requestData));

        $payment->refresh();

        Http::assertSentCount(1);

        $this->assertSame($payment->status, PaymentStatus::FAILED);

        $response->assertViewIs('irpayment::invalid');
        $response->assertViewHasAll([
            'payment' => fn (Payment $actualPayment) => $actualPayment->is($payment),
            'verification' => fn (VerificationValueObject $verification) => ! $verification->cardHash
                && ! $verification->cardMask
                && ! $verification->referenceId,
        ]);
    }

    #[TestDox('The transaction has already been verified')]
    public function test_payment_verification_status_conflict(): void
    {
        $paymentCode = (string) fake()->randomNumber();
        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->pending()
            ->for($order, 'paymentable')
            ->state(['authority_key' => $paymentCode])
            ->create();

        $requestData = [
            'status' => '1',
            'errorCode' => 110,
            'data' => json_encode([
                'clientRefId' => $payment->id,
                'paymentCode' => $paymentCode,
                'amount' => $payment->amount,
                'gatewayAmount' => $payment->amount,

            ]),
        ];
        // mock verify request
        $requestResponse = file_get_contents(workbench_path('mock/payping/verify-409.json'));

        Http::fake([
            'https://api.zarinpal.com/pg/v4/payment/verify.json' => Http::response($requestResponse, 409),
        ]);

        $response = $this->get(route('irpayment.payment.payping.verify', $requestData));

        $payment->refresh();

        $this->assertSame($payment->status, PaymentStatus::COMPLETE);

        $response->assertViewIs('irpayment::verify');
        $response->assertViewHasAll([
            'payment' => fn (Payment $actualPayment) => $actualPayment->is($payment),
            'verification' => fn (VerificationValueObject $vo) => $vo->code == 101
                && $vo->message == trans('irpayment::messages.payping.409'),
        ]);

        Http::assertSentCount(1);

        Event::assertNotDispatched(PaymentCanceled::class);
        Event::assertNotDispatched(PaymentFailed::class);
        Event::assertDispatched(PaymentVerified::class);
    }
}
