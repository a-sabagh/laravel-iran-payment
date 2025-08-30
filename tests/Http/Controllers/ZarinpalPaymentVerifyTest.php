<?php

namespace IRPayment\Tests\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\MessageBag;
use IRPayment\Enums\ZarinpalVerificationStatus;
use IRPayment\Models\Payment;
use IRPayment\Tests\TestCase;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class ZarinpalPaymentVerifyTest extends TestCase
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
    }
}
