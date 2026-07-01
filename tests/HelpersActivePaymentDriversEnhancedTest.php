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
                        'description' => 'Zarinpal Gateway Description',
                    ],
                ],
                [
                    [
                        'id' => 'zarinpal',
                        'text' => 'Zarinpal Gateway',
                        'caption' => 'Zarinpal Gateway Description',
                    ],
                ],
            ],
            'inactive driver is not returned' => [
                [
                    'zarinpal' => [
                        'active' => false,
                        'title' => 'Zarinpal Gateway',
                        'description' => 'Zarinpal Gateway Description',
                    ],
                ],
                [],
            ],
            'active driver falls back to translated title' => [
                [
                    'zarinpal' => [
                        'active' => true,
                        'title' => 'Zarinpal',
                        'description' => 'Zarinpal Gateway Description',
                    ],
                ],
                [
                    [
                        'id' => 'zarinpal',
                        'text' => 'Zarinpal',
                        'caption' => 'Zarinpal Gateway Description',
                    ],
                ],
            ],
            'only active drivers are returned as enhanced options' => [
                [
                    'zarinpal' => [
                        'active' => true,
                        'title' => 'Zarinpal Gateway',
                        'description' => 'Zarinpal Gateway Description',
                    ],
                    'payping' => [
                        'active' => false,
                        'title' => 'Payping Gateway',
                        'description' => 'Payping Gateway Description',
                    ],
                    'credit' => [
                        'active' => true,
                        'title' => 'Wallet Credit',
                        'description' => 'Wallet Credit Description',
                    ],
                ],
                [
                    [
                        'id' => 'zarinpal',
                        'text' => 'Zarinpal Gateway',
                        'caption' => 'Zarinpal Gateway Description',
                    ],
                    [
                        'id' => 'credit',
                        'text' => 'Wallet Credit',
                        'caption' => 'Wallet Credit Description',
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
