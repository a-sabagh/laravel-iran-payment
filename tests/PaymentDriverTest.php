<?php

namespace IRPayment\Tests;

use IRPayment\Drivers\Zarinpal;
use IRPayment\Facades\IRPayment;
use IRPayment\PaymentDriverManager;

class PaymentDriverTest extends TestCase
{
    public function test_payment_driver_can_instanciate_zarinpal(): void
    {
        $zarinpal = $this->app->make(PaymentDriverManager::class)->driver('zarinpal');

        $this->assertInstanceOf(Zarinpal::class, $zarinpal);
    }

    public function test_payment_facade_room_object(): void
    {
        $actualPaymentManager = IRPayment::getFacadeRoot();

        $this->assertInstanceOf(PaymentDriverManager::class, $actualPaymentManager);
    }
}
