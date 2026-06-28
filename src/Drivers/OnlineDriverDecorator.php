<?php

namespace IRPayment\Drivers;

use IRPayment\Contracts\OnlineChannel;
use IRPayment\DTO\ProcessResponseValueObject;
use IRPayment\Exceptions\PaymentDriverNotActive;
use IRPayment\Models\Payment;

/**
 * @method title(): string
 * @method description(): string
 * @method CallbackUrl(): string
 * @method startPay(string $authorityKey)
 * @method request(Payment $payment): array
 * @method config(): \Illuminate\Support\Collection
 * @method channel(): \IRPayment\Enums\PaymentChannel
 * @method verify(Payment $payment): \IRPayment\DTO\VerificationValueObject
 */
class OnlineDriverDecorator
{
    public function __construct(
        public OnlineChannel $driver
    ) {}

    /**
     * Decorate process payment based on driver activation
     * This deocration implement just on online drivers.
     *
     * @see \IRPayment\Contracts\OnlineChannel
     */
    public function process(Payment $payment): ProcessResponseValueObject
    {
        if (! $this->driver->config->get('active')) {
            $name = $this->getDriverKey($this->driver);

            throw new PaymentDriverNotActive("Driver {$name} is deactive");
        }

        return $this->driver->process($payment);
    }

    /** @see \IRPayment\PaymentDriverManager */
    protected function getDriverKey(OnlineChannel $driver): string
    {
        // Reverse class name to driver key
        // from pascal case to lower snake
        return str(class_basename($driver))->snake()->lower();
    }

    /**
     * Proxy dynamic method calls to the decorated online driver.
     *
     * @see \IRPayment\Tests\OnlineDriverDecoratorMagicCallTest
     * @see \IRPayment\DTO\PaymentMethodValueObject::normalizedDriver
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->driver->{$method}(...$arguments);
    }
}
