<?php

namespace IRPayment\Tests;

use IRPayment\Drivers\Zarinpal;
use IRPayment\PaymentDriverManager;

class PaymentDriverTest extends TestCase
{
    public function test_payment_driver_can_instanciate_zarinpal(): void
    {
        $zarinpal = $this->app->make(PaymentDriverManager::class)->driver('zarinpal');

        $this->assertInstanceOf(Zarinpal::class, $zarinpal);
    }
}
