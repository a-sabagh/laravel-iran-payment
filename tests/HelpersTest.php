<?php

namespace IRPayment\Tests;

use IRPayment\Enums\PaymentChannel;

use function IRPayment\get_available_payment_drivers;

class HelpersTest extends TestCase
{
    public function test_irpayment_available_payment_drivers(): void
    {
        $this->app->config->set('irpayment.drivers', [
            'zarinpal' => [
                'channel' => PaymentChannel::ONLINE,
            ],
            'saman' => [
                'channel' => PaymentChannel::ONLINE,
            ],
            'bank_transfer' => [
                'channel' => PaymentChannel::OFFLINE,
            ],
        ]);

        $availablePaymentDrivers = get_available_payment_drivers();

        $this->assertSame(['zarinpal', 'saman'], $availablePaymentDrivers);
    }
}
