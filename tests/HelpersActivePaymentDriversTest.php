<?php

namespace IRPayment\Tests;

use PHPUnit\Framework\Attributes\DataProvider;

use function IRPayment\get_active_payment_drivers;

class HelpersActivePaymentDriversTest extends TestCase
{
    public static function paymentDriversProvider(): array
    {
        return [
            'active driver is returned' => [
                [
                    'zarinpal' => [
                        'active' => true,
                    ],
                ],
                ['zarinpal'],
            ],
            'inactive driver is not returned' => [
                [
                    'zarinpal' => [
                        'active' => false,
                    ],
                ],
                [],
            ],
            'only active drivers are returned' => [
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
                ['zarinpal', 'paykan'],
            ],
        ];
    }

    #[DataProvider('paymentDriversProvider')]
    public function test_irpayment_active_payment_drivers(array $drivers, array $expected): void
    {
        $this->app->config->set('irpayment.drivers', $drivers);

        $this->assertSame($expected, get_active_payment_drivers());
    }
}
