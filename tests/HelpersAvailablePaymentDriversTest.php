<?php

namespace IRPayment\Tests;

use IRPayment\Enums\PaymentChannel;
use PHPUnit\Framework\Attributes\DataProvider;

use function IRPayment\get_available_payment_drivers;

class HelpersAvailablePaymentDriversTest extends TestCase
{
    public static function availablePaymentDriversProvider(): array
    {
        return [
            'active online driver is available' => [
                [
                    'zarinpal' => [
                        'active' => true,
                        'channel' => PaymentChannel::ONLINE,
                    ],
                ],
                ['zarinpal'],
            ],
            'inactive online driver is unavailable' => [
                [
                    'zarinpal' => [
                        'active' => false,
                        'channel' => PaymentChannel::ONLINE,
                    ],
                ],
                [],
            ],
            'active offline driver is unavailable' => [
                [
                    'card_transfer' => [
                        'active' => true,
                        'channel' => PaymentChannel::OFFLINE,
                    ],
                ],
                [],
            ],
            'only active online drivers are available' => [
                [
                    'zarinpal' => [
                        'active' => true,
                        'channel' => PaymentChannel::ONLINE,
                    ],
                    'payping' => [
                        'active' => false,
                        'channel' => PaymentChannel::ONLINE,
                    ],
                    'paykan' => [
                        'active' => true,
                        'channel' => PaymentChannel::ONLINE,
                    ],
                    'card_transfer' => [
                        'active' => true,
                        'channel' => PaymentChannel::OFFLINE,
                    ],
                ],
                ['zarinpal', 'paykan'],
            ],
        ];
    }

    #[DataProvider('availablePaymentDriversProvider')]
    public function test_irpayment_available_payment_drivers(array $drivers, array $expected): void
    {
        $this->app->config->set('irpayment.drivers', $drivers);

        $this->assertSame($expected, get_available_payment_drivers());
    }
}
