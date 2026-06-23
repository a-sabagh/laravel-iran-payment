<?php

namespace IRPayment\Tests;

use PHPUnit\Framework\Attributes\DataProvider;

use function IRPayment\get_deactive_payment_drivers;

class HelpersDeactivePaymentDriversTest extends TestCase
{
    public static function paymentDriversProvider(): array
    {
        return [
            'inactive driver is returned' => [
                [
                    'zarinpal' => [
                        'active' => false,
                    ],
                ],
                ['zarinpal'],
            ],
            'active driver is not returned' => [
                [
                    'zarinpal' => [
                        'active' => true,
                    ],
                ],
                [],
            ],
            'only inactive drivers are returned' => [
                [
                    'zarinpal' => [
                        'active' => true,
                    ],
                    'payping' => [
                        'active' => false,
                    ],
                    'paykan' => [
                        'active' => true,
                    ],
                    'card_transfer' => [
                        'active' => false,
                    ],
                ],
                ['payping', 'card_transfer'],
            ],
        ];
    }

    #[DataProvider('paymentDriversProvider')]
    public function test_irpayment_deactive_payment_drivers(array $drivers, array $expected): void
    {
        $this->app->config->set('irpayment.drivers', $drivers);

        $this->assertSame($expected, get_deactive_payment_drivers());
    }
}
