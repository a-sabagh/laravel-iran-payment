<?php

namespace IRPayment\Tests\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\MessageBag;
use IRPayment\DTO\VerificationValueObject;
use IRPayment\Enums\PaymentStatus;
use IRPayment\Enums\ZarinpalVerificationStatus;
use IRPayment\Events\PaymentCancelled;
use IRPayment\Events\PaymentFailed;
use IRPayment\Events\PaymentVerified;
use IRPayment\Models\Payment;
use IRPayment\Tests\TestCase;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\package_path;
use function Orchestra\Testbench\workbench_path;

class ZarinpalPaymentVerifyTest extends TestCase
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
        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->for($order, 'paymentable')
            ->create();

        $requestData = [
            'authority' => $payment->authority_key,
            'status' => 'invalid-zarinpal-status',
        ];

        $response = $this->get(route('irpayment.payment.zarinpal.verify', $requestData));

        $response->assertViewIs('irpayment::invalid');
        $response->assertViewHas('errors', function (MessageBag $errors) {
            return
                $errors->has('status') &&
                $errors->missing('authority');
        });

        Http::assertNothingSent();

        Event::assertNotDispatched(PaymentCancelled::class);
        Event::assertNotDispatched(PaymentFailed::class);
        Event::assertNotDispatched(PaymentVerified::class);
    }

    public function test_payment_verification_authority_key_invalid(): void
    {
        $authorityKey = fake()->unique()->regexify('A00000[A-Z0-9a-z]{32,40}');

        $requestData = [
            'authority' => $authorityKey,
            'status' => ZarinpalVerificationStatus::SUCCESS,
        ];

        $response = $this->get(route('irpayment.payment.zarinpal.verify', $requestData));

        $response->assertViewHas('errors', function (MessageBag $errors) {
            return
                $errors->missing('status') &&
                $errors->has('authority');
        });

        Http::assertNothingSent();

        Event::assertNotDispatched(PaymentCancelled::class);
        Event::assertNotDispatched(PaymentFailed::class);
        Event::assertNotDispatched(PaymentVerified::class);
    }

    public function test_payment_verification_success(): void
    {
        $requestResponse = file_get_contents(package_path('tests/fake/zarinpal/verify.json'));

        Http::fake([
            'https://api.zarinpal.com/pg/v4/payment/verify.json' => Http::response($requestResponse, 200),
        ]);

        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->processing()
            ->for($order, 'paymentable')
            ->create();

        $requestData = [
            'authority' => $payment->authority_key,
            'status' => ZarinpalVerificationStatus::SUCCESS,
        ];

        $response = $this->get(route('irpayment.payment.zarinpal.verify', $requestData));

        $payment->refresh();

        $response->assertOk();
        $response->assertViewHas('payment', function ($actualPayment) {
            return
                $actualPayment->code == 100 &&
                $actualPayment->reference_id == 201 &&
                $actualPayment->status == PaymentStatus::PROCESSING &&
                $actualPayment->card_hash == '1EBE3EBEBE35C7EC0F8D6EE4F2F859107A87822CA179BC9528767EA7B5489B69' &&
                $actualPayment->card_mask == '502229******5995';
        });

        $response->assertViewIs('irpayment::verify');
        $response->assertViewHas(
            'verification', fn ($verification) => $verification->message == 'Success'
        );

        Event::assertDispatched(PaymentVerified::class, function ($event) use ($payment) {
            return $event->payment->is($payment)
                && $event->verification instanceof VerificationValueObject
                && $event->verification->code == 100;
        });
    }

    public function test_payment_verification_failed(): void
    {
        $requestResponse = file_get_contents(package_path('tests/fake/zarinpal/verify-61.json'));

        Http::fake([
            'https://api.zarinpal.com/pg/v4/payment/verify.json' => Http::response($requestResponse, 200),
        ]);

        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->processing()
            ->for($order, 'paymentable')
            ->create();

        $requestData = [
            'authority' => $payment->authority_key,
            'status' => ZarinpalVerificationStatus::SUCCESS,
        ];

        $response = $this->get(route('irpayment.payment.zarinpal.verify', $requestData));

        $response->assertViewIs('irpayment::invalid');
        $response->assertViewHasAll([
            'verification' => fn ($verification) => Lang::get('irpayment::messages.zarinpal.-61') == $verification->message,
            'payment' => function ($actualPayment) {
                return
                    $actualPayment->code == -61 &&
                    $actualPayment->status == PaymentStatus::FAILED;
            },
        ]);

        Http::assertSentCount(1);

        Event::assertDispatched(PaymentFailed::class, function ($event) use ($payment) {
            $payment->refresh();

            return $event->payment->is($payment)
                && $event->verification instanceof VerificationValueObject
                && $event->verification->code == -61;
        });
    }

    public function test_payment_verification_cancelled(): void
    {
        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->processing()
            ->for($order, 'paymentable')
            ->create();

        $requestData = [
            'authority' => $payment->authority_key,
            'status' => ZarinpalVerificationStatus::CANCELED,
        ];

        $response = $this->get(route('irpayment.payment.zarinpal.verify', $requestData));

        $response->assertViewHasAll([
            'payment' => $payment,
        ]);

        $payment->refresh();

        $this->assertSame($payment->status, PaymentStatus::CANCELED);

        Http::assertNothingSent();
        Event::assertDispatched(PaymentCancelled::class);
    }

    public function test_prevent_cancelling_payment_which_already_completed(): void
    {
        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->complete()
            ->for($order, 'paymentable')
            ->create();

        $requestData = [
            'authority' => $payment->authority_key,
            'status' => ZarinpalVerificationStatus::CANCELED,
        ];

        $response = $this->get(route('irpayment.payment.zarinpal.verify', $requestData));

        $response->assertViewIs('irpayment::invalid');
        $response->assertViewHas('errors', fn (MessageBag $errors) => $errors->has('payment')
        );
        $payment->refresh();

        $this->assertSame($payment->status, PaymentStatus::COMPLETE);
        Http::assertNothingSent();
    }
}
