<?php

namespace IRPayment\Tests;

use IRPayment\Enums\PaymentChannel;
use IRPayment\Models\Payment;

class PaymentChannelAttributeTest extends TestCase
{
    public function test_online_attribute_is_true_for_online_payments(): void
    {
        $payment = new Payment([
            'payment_channel' => PaymentChannel::ONLINE,
        ]);

        $this->assertTrue($payment->online);
        $this->assertFalse($payment->offline);
    }

    public function test_offline_attribute_is_true_for_offline_payments(): void
    {
        $payment = new Payment([
            'payment_channel' => PaymentChannel::OFFLINE,
        ]);

        $this->assertFalse($payment->online);
        $this->assertTrue($payment->offline);
    }
}
