<?php

namespace IRPayment\Tests;

use IRPayment\Facades\IRPayment;
use PHPUnit\Framework\Attributes\DependsExternal;

class PaymentDriverImplementionTest extends TestCase
{
    #[DependsExternal(PaymentDriverTest::class, 'test_payment_facade_room_object')]
    public function test_zarinpal_payment_driver_attributes_based_locale(): void
    {
        $zarinpalPaymentDriver = IRPayment::driver('zarinpal');

        $this->assertSame('Zarinpal', $zarinpalPaymentDriver->title());
        $this->assertSame('Pay securely online via the Zarinpal payment gateway.', $zarinpalPaymentDriver->description());

        $this->app->setLocale('fa');
        $this->assertSame('زرین‌پال', $zarinpalPaymentDriver->title());
    }

    #[DependsExternal(PaymentDriverTest::class, 'test_payment_facade_room_object')]
    public function test_card_transfer_payment_driver_attributes(): void
    {
        $CardTransferPaymentDriver = IRPayment::driver('card_transfer');

        $this->assertSame('Card Transfer', $CardTransferPaymentDriver->title());
    }
}
