<?php

namespace IRPayment\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use IRPayment\Models\Payment;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public function test_payment_model_authority_scope(): void
    {
        $order = Order::factory()->create();

        $payment = Payment::factory()->for(
            $order, 'paymentable'
        )->create();

        $this->assertDatabaseCount('payments', 1);

        $authorityKey = $payment->authority_key;

        $paymentCollection = Payment::authorityKey($authorityKey)->get();

        $this->assertTrue($paymentCollection->contains($payment));

    }
}
