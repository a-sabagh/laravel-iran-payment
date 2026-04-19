<?php

namespace IRPayment\Tests\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\MessageBag;
use IRPayment\DTO\VerificationValueObject;
use IRPayment\Enums\PaymentStatus;
use IRPayment\Events\PaymentFailed;
use IRPayment\Events\PaymentVerified;
use IRPayment\Facades\IRPayment;
use IRPayment\Models\Payment;
use IRPayment\Tests\TestCase;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class PaykanPaymentVerifyTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public function test_payment_verification_status_invalid(): void
    {
        Event::fake();

        $response = $this->get(route('irpayment.payment.paykan.verify', [
            'ref_num' => fake()->uuid(),
            'order_id' => fake()->randomNumber(),
        ]));

        $response->assertViewIs('irpayment::invalid');

        $response->assertViewHas('errors', fn (MessageBag $errors) => $errors->has('ref_num')
            && $errors->has('tracking_code')
            && $errors->missing('order_id'));

        Http::assertNothingSent();

        Event::assertNotDispatched(PaymentFailed::class);
        Event::assertNotDispatched(PaymentVerified::class);
    }

    public function test_payment_verification_failed(): void
    {
        Event::fake();

        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->processing()
            ->for($order, 'paymentable')
            ->create();

        $verification = new VerificationValueObject(
            code: 400,
            message: 'Paykan verification failed.',
            cardHash: null,
            cardMask: null,
            referenceId: null,
        );

        IRPayment::shouldReceive('driver->verify')
            ->once()
            ->with($payment->amount, [
                'order_id' => (string) $order->id,
                'tracking_code' => 'tracking-123',
                'ref_num' => $payment->authority_key,
            ])
            ->andReturn($verification);

        $response = $this->get(route('irpayment.payment.paykan.verify', [
            'ref_num' => $payment->authority_key,
            'order_id' => (string) $order->id,
            'tracking_code' => 'tracking-123',
        ]));

        $payment->refresh();

        $response->assertViewIs('irpayment::invalid');
        $response->assertViewHasAll([
            'payment' => fn (Payment $actualPayment) => $actualPayment->is($payment),
            'verification' => fn (VerificationValueObject $actualVerification) => $actualVerification->code === 400
                && $actualVerification->message === 'Paykan verification failed.',
        ]);

        $this->assertSame(PaymentStatus::FAILED, $payment->status);
        $this->assertSame(400, $payment->code);

        Http::assertNothingSent();
        Event::assertDispatched(PaymentFailed::class);
        Event::assertNotDispatched(PaymentVerified::class);
    }

    public function test_payment_verify_in_happy_path(): void
    {
        Event::fake();

        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->processing()
            ->for($order, 'paymentable')
            ->create();

        $verification = new VerificationValueObject(
            code: 100,
            message: 'Payment verified.',
            cardHash: $cardHash = hash('sha256', '6037991234561234'),
            cardMask: '6037******1234',
            referenceId: 987654,
        );

        IRPayment::shouldReceive('driver->verify')
            ->once()
            ->with($payment->amount, [
                'order_id' => (string) $order->id,
                'tracking_code' => 'tracking-456',
                'ref_num' => $payment->authority_key,
            ])
            ->andReturn($verification);

        $response = $this->get(route('irpayment.payment.paykan.verify', [
            'ref_num' => $payment->authority_key,
            'order_id' => (string) $order->id,
            'tracking_code' => 'tracking-456',
        ]));

        $payment->refresh();

        $response->assertOk();
        $response->assertViewIs('irpayment::verify');
        $response->assertViewHasAll([
            'payment' => fn (Payment $actualPayment) => $actualPayment->is($payment),
            'verification' => fn (VerificationValueObject $actualVerification) => $actualVerification->code === 100
                && $actualVerification->referenceId === 987654,
        ]);

        $this->assertSame(PaymentStatus::COMPLETE, $payment->status);
        $this->assertSame(100, $payment->code);
        $this->assertSame(987654, $payment->reference_id);
        $this->assertSame($cardHash, $payment->card_hash);
        $this->assertSame('6037******1234', $payment->card_mask);

        Http::assertNothingSent();

        Event::assertNotDispatched(PaymentFailed::class);
        Event::assertDispatched(PaymentVerified::class);
    }
}
