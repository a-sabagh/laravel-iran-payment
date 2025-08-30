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

    public function test_zarinpal_verify_success(): void
    {
        $requestResponse = file_get_contents(__DIR__.'/fake/zarinpal/verify.json');

        Http::fake([
            'https://api.zarinpal.com/pg/v4/payment/verify.json' => Http::response($requestResponse, 200),
        ]);

        $amount = fake()->numberBetween(1000, 9999);
        $authorityKey = fake()->unique()->regexify('A00000[A-Z0-9a-z]{32,40}');

        $verificationVO = IRPayment::driver('zarinpal')->verify($amount, $authorityKey);

        $this->assertInstanceOf(VerificationValueObject::class, $verificationVO);
        $this->assertSame(100, $verificationVO->code);
        $this->assertSame('Success', $verificationVO->message);
        $this->assertSame('1EBE3EBEBE35C7EC0F8D6EE4F2F859107A87822CA179BC9528767EA7B5489B69', $verificationVO->cardHash);
        $this->assertSame('502229******5995', $verificationVO->cardMask);
        $this->assertSame(201, $verificationVO->referenceId);

        $this->assertTrue($verificationVO->isSuccess());
        $this->assertFalse($verificationVO->isFailed());
    }

    public function test_zarinpal_verify_failed(): void
    {
        $requestResponse = file_get_contents(__DIR__.'/fake/zarinpal/verify-61.json');

        Http::fake([
            'https://api.zarinpal.com/pg/v4/payment/verify.json' => Http::response($requestResponse, 200),
        ]);

        $amount = fake()->numberBetween(1000, 9999);
        $authorityKey = fake()->unique()->regexify('A00000[A-Z0-9a-z]{32,40}');

        $verificationVO = IRPayment::driver('zarinpal')->verify($amount, $authorityKey);

        $this->assertInstanceOf(VerificationValueObject::class, $verificationVO);
        $this->assertSame(-61, $verificationVO->code);
        $this->assertSame('Session is not in success status.', $verificationVO->message);
        $this->assertNull($verificationVO->cardHash);
        $this->assertNull($verificationVO->cardMask);
        $this->assertNull($verificationVO->referenceId);

        $this->assertFalse($verificationVO->isSuccess());
        $this->assertTrue($verificationVO->isFailed());
    }
}
