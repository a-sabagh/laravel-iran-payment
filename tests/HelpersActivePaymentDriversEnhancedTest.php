<?php

namespace IRPayment\Tests;

use PHPUnit\Framework\Attributes\DataProvider;

use function IRPayment\get_active_payment_drivers_enhance_options;

class HelpersActivePaymentDriversEnhancedTest extends TestCase
{
    public static function paymentDriversProvider(): array
    {
        return [
            'active driver returns id and configured title as text' => [
                [
                    'zarinpal' => [
                        'active' => true,
                        'title' => 'Zarinpal Gateway',
                    ],
                ],
                [
                    [
                        'id' => 'zarinpal',
                        'text' => 'Zarinpal Gateway',
                    ],
                ],
            ],
            'inactive driver is not returned' => [
                [
                    'zarinpal' => [
                        'active' => false,
                        'title' => 'Zarinpal Gateway',
                    ],
                ],
                [],
            ],
            'active driver falls back to translated title' => [
                [
                    'zarinpal' => [
                        'active' => true,
                    ],
                ],
                [
                    [
                        'id' => 'zarinpal',
                        'text' => 'Zarinpal',
                    ],
                ],
            ],
            'only active drivers are returned as enhanced options' => [
                [
                    'zarinpal' => [
                        'active' => true,
                        'title' => 'Zarinpal Gateway',
                    ],
                    'payping' => [
                        'active' => false,
                        'title' => 'Payping Gateway',
                    ],
                    'credit' => [
                        'active' => true,
                        'title' => 'Wallet Credit',
                    ],
                ],
                [
                    [
                        'id' => 'zarinpal',
                        'text' => 'Zarinpal Gateway',
                    ],
                    [
                        'id' => 'credit',
                        'text' => 'Wallet Credit',
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('paymentDriversProvider')]
    public function test_irpayment_active_payment_drivers_enhance_options(array $drivers, array $expected): void
    {
        $this->app->config->set('irpayment.drivers', $drivers);

        $this->assertSame($expected, get_active_payment_drivers_enhance_options());
    }
}
