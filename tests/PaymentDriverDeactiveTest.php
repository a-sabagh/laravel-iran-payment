<?php

namespace IRPayment\Tests;

use IRPayment\Exceptions\PaymentDriverNotActive;
use IRPayment\Facades\IRPayment;

class PaymentDriverDeactiveTest extends TestCase
{
    public function test_deactive_payment_driver_throws_exception(): void
    {
        $this->app->config->set('irpayment.drivers.zarinpal.active', false);

        $this->expectException(PaymentDriverNotActive::class);

        $this->expectExceptionMessage('Driver zarinpal is deactive');

        IRPayment::driver('zarinpal');
    }
}
