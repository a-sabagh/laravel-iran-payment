<?php

namespace IRPayment\Tests;

use BadMethodCallException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use IRPayment\DTO\PaymentMethodValueObject;
use IRPayment\Enums\PaymentChannel;
use IRPayment\Models\Payment;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

class PaymentMethodCastTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public function test_zarinpal_payment_method_vo_title_driver(): void
    {
        $this->app->config->set('irpayment.drivers.zarinpal.title', 'zarinberar');
        $this->app->config->set('irpayment.drivers.zarinpal.description', 'foo description');

        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->state([
                'payment_method' => 'zarinpal',
            ])
            ->for($order, 'paymentable')
            ->create();

        $this->assertInstanceOf(PaymentMethodValueObject::class, $payment->payment_method);
        $this->assertSame('zarinberar', $payment->payment_method->title);
        $this->assertSame('foo description', $payment->payment_method->description);
        $this->assertSame(PaymentChannel::ONLINE, $payment->payment_method->channel);
    }

    public function test_payment_method_can_be_nullable_without_exception(): void
    {
        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->state([
                'payment_method' => null,
            ])
            ->for($order, 'paymentable')
            ->create();

        $this->assertNull($payment->payment_method);
    }

    public function test_payment_method_thorws_invalid_argument_exception_by_manager(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $payment = Payment::factory()
            ->state([
                'payment_method' => 'foo',
            ])
            ->make();

        $payment->payment_method->title;
    }

    public function test_payment_method_thorws_unexpected_value_exception(): void
    {
        $this->expectException(BadMethodCallException::class);

        $payment = Payment::factory()
            ->state([
                'payment_method' => 'zarinpal',
            ])
            ->make();

        $payment->payment_method->foo;
    }

    public function test_payment_method_set_payment_method_value_object_on_the_fly(): void
    {
        $this->app->config->set('irpayment.drivers.zarinpal.title', 'zarinberar');
        $this->app->config->set('irpayment.drivers.zarinpal.description', 'foo description');
        
        $order = Order::factory()->create();
        
        $payment = Payment::factory()
            ->state([
                'payment_method' => null,
            ])
            ->make();

        $payment->paymentable()->associate($order);
        $payment->save();

        $paymentMethodVO = new PaymentMethodValueObject('zarinpal');

        $payment->payment_method = $paymentMethodVO;

        $this->assertInstanceOf(PaymentMethodValueObject::class, $payment->payment_method);
        $this->assertSame('zarinberar', $payment->payment_method->title);
        $this->assertSame('foo description', $payment->payment_method->description);
        $this->assertSame(PaymentChannel::ONLINE, $payment->payment_method->channel);
    }
}
