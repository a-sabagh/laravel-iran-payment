<?php

namespace IRPayment\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use IRPayment\Enums\PaymentChannel;
use IRPayment\Enums\PaymentStatus;
use IRPayment\Models\Payment;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class PaymentModelAttrCastingTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public function test_payment_model_casts_attributes_to_expected_types(): void
    {
        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->for($order, 'paymentable')
            ->create([
                'code' => '200',
                'payment_channel' => PaymentChannel::ONLINE->value,
                'amount' => '1555000',
                'status' => PaymentStatus::PROCESSING->value,
                'metadata' => [
                    'mobile' => '09120000000',
                    'items' => ['sku-1', 'sku-2'],
                ],
                'reference_id' => '1234567890',
            ]);

        $payment->refresh();

        $this->assertIsInt($payment->code);
        $this->assertSame(200, $payment->code);

        $this->assertInstanceOf(PaymentChannel::class, $payment->payment_channel);
        $this->assertSame(PaymentChannel::ONLINE, $payment->payment_channel);

        $this->assertIsInt($payment->amount);
        $this->assertSame(1555000, $payment->amount);

        $this->assertInstanceOf(PaymentStatus::class, $payment->status);
        $this->assertSame(PaymentStatus::PROCESSING, $payment->status);

        $this->assertIsArray($payment->metadata);
        $this->assertSame([
            'mobile' => '09120000000',
            'items' => ['sku-1', 'sku-2'],
        ], $payment->metadata);

        $this->assertIsInt($payment->reference_id);
        $this->assertSame(1234567890, $payment->reference_id);
    }
}
