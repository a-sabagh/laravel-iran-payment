<?php

namespace IRPayment\Tests\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use IRPayment\Facades\IRPayment;
use IRPayment\Models\Payment;
use IRPayment\Tests\TestCase;
use Mockery;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public function test_payment_model_route_model_binding(): void
    {
        $order = Order::factory()->create();

        $payment = Payment::factory()->for(
            $order, 'paymentable'
        )->create();

        IRPayment::shouldReceive('driver->verify')
            ->andReturnTrue();

        $response = $this->get("payment/verify/{$payment->authority_key}");

        $response->assertViewHas('payment', $payment);
    }

    public function test_payment_verify_mock_driver(): void
    {
        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->state(['payment_method' => 'zarinpal'])
            ->for($order, 'paymentable')
            ->create();

        IRPayment::shouldReceive('driver')
            ->once()
            ->with('zarinpal')
            ->andReturnSelf()
            ->shouldReceive('verify')
            ->with(Mockery::any())
            ->andReturnTrue();

        $this->get("payment/verify/{$payment->authority_key}");
    }
}
