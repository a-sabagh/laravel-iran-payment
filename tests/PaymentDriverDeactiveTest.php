<?php

namespace IRPayment\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use IRPayment\Exceptions\PaymentDriverNotActive;
use IRPayment\Facades\IRPayment;
use IRPayment\Models\Payment;
use PHPUnit\Framework\Attributes\DataProvider;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class PaymentDriverDeactiveTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public static function driverProvider(): array
    {
        return [
            ['zarinpal'],
            ['payping'],
            ['paykan'],
        ];
    }

    #[DataProvider('driverProvider')]
    public function test_deactive_payment_driver_throws_exception(string $driver): void
    {
        $this->app->config->set("irpayment.drivers.{$driver}.active", false);

        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->for($order, 'paymentable')->create();

        $this->expectException(PaymentDriverNotActive::class);

        $this->expectExceptionMessage("Driver {$driver} is deactive");

        IRPayment::driver($driver)->process($payment);
    }
}
