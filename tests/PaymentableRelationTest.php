<?php

namespace IRPayment\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use IRPayment\Models\Payment;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class PaymentableRelationTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public function test_payment_morph_on_order(): void
    {
        $order = Order::factory()->create();

        $payment = Payment::factory()->for(
            $order, 'paymentable'
        )->create();

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertInstanceOf(Order::class, $payment->paymentable);
        $this->asserttrue($order->is($payment->paymentable));
    }

    public function test_order_payment_relation_factory(): void
    {
        $order = Order::factory()->hasPayments(1)->create();

        $this->assertInstanceOf(Payment::class, $order->payments->first());
    }
}
