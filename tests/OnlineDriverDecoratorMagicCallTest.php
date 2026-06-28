<?php

namespace IRPayment\Tests;

use IRPayment\Drivers\OnlineDriverDecorator;
use IRPayment\Drivers\Paykan;
use IRPayment\Drivers\Payping;
use IRPayment\Drivers\Zarinpal;
use IRPayment\DTO\VerificationValueObject;
use PHPUnit\Framework\Attributes\DataProvider;

class OnlineDriverDecoratorMagicCallTest extends TestCase
{
    public static function onlineDriverProvider(): array
    {
        return [
            'zarinpal' => [Zarinpal::class],
            'payping' => [Payping::class],
            'paykan' => [Paykan::class],
        ];
    }

    #[DataProvider('onlineDriverProvider')]
    public function test_magic_call_proxies_driver_methods(string $driverClass): void
    {
        /** @var \IRPayment\Contracts\OnlineChannel $driver */
        $driver = $this->getMockBuilder($driverClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['title'])
            ->getMock();

        $driver->expects($this->once())
            ->method('title')
            ->with()
            ->willReturn('Mocked Online Driver');

        $decorator = new OnlineDriverDecorator($driver);

        $this->assertSame('Mocked Online Driver', $decorator->title());
    }

    #[DataProvider('onlineDriverProvider')]
    public function test_magic_call_passes_arguments_to_driver_methods(string $driverClass): void
    {
        /** @var \IRPayment\Contracts\OnlineChannel $driver */
        $verification = new VerificationValueObject(
            code: 100,
            referenceId: 123456,
            message: 'Payment verified.',
            cardHash: 'card-hash',
            cardMask: 'card-mask'
        );

        $credentials = ['authority_key' => 'authority'];

        $driver = $this->getMockBuilder($driverClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['verify'])
            ->getMock();

        $driver->expects($this->once())
            ->method('verify')
            ->with(10000, $credentials)
            ->willReturn($verification);

        $decorator = new OnlineDriverDecorator($driver);

        $this->assertSame($verification, $decorator->verify(10000, $credentials));
    }
}
