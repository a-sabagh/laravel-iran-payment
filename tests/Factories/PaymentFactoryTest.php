<?php

namespace Tests\Feature\Factories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use IRPayment\Enums\PaymentChannel;
use IRPayment\Models\Payment;
use IRPayment\Tests\TestCase;
use Orchestra\Testbench\Attributes\WithConfig;

#[WithConfig('database.default', 'testing')]
class PaymentFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_offline_state(): void
    {
        $payment = Payment::factory()->offline()->make();

        $this->assertNull($payment->payment_method);
        $this->assertSame(PaymentChannel::OFFLINE, $payment->payment_channel);
    }
}
