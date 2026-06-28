<?php

namespace IRPayment\Tests;

use IRPayment\Drivers\CardTransfer;
use IRPayment\Drivers\Credit;
use IRPayment\Drivers\OnlineDriverDecorator;
use IRPayment\Drivers\Paykan;
use IRPayment\Drivers\Payping;
use IRPayment\Drivers\Zarinpal;
use IRPayment\Facades\IRPayment;
use IRPayment\PaymentDriverManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;

class PaymentDriverTest extends TestCase
{
    public static function onlinerDriverProvider(): array
    {
        return [
            [
                'driver' => 'payping',
                'class' => Payping::class,
            ],
            [
                'driver' => 'zarinpal',
                'class' => Zarinpal::class,
            ],
            [
                'driver' => 'paykan',
                'class' => Paykan::class,
            ],
        ];
    }

    #[DataProvider('onlinerDriverProvider')]
    public function test_payment_driver_can_instanciate_payping(string $driver, string $class): void
    {
        $decorator = $this->app->make(PaymentDriverManager::class)->driver($driver);

        $this->assertInstanceOf(OnlineDriverDecorator::class, $decorator);
        $this->assertInstanceOf($class, $decorator->driver);
    }

    public function test_payment_facade_room_object(): void
    {
        $actualPaymentManager = IRPayment::getFacadeRoot();

        $this->assertInstanceOf(PaymentDriverManager::class, $actualPaymentManager);
    }

    #[Depends('test_payment_facade_room_object')]
    public function test_payment_manager_can_instanciate_card_transfer_driver(): void
    {
        $cardTransferDriver = IRPayment::driver('card_transfer');

        $this->assertInstanceOf(CardTransfer::class, $cardTransferDriver);
    }

    #[Depends('test_payment_facade_room_object')]
    public function test_payment_manager_can_instanciate_credit_driver(): void
    {
        $creditDriver = IRPayment::driver('credit');

        $this->assertInstanceOf(Credit::class, $creditDriver);
    }
}
