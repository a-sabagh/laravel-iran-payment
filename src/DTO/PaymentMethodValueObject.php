<?php

namespace IRPayment\DTO;

use BadMethodCallException;
use IRPayment\Contracts\PaymentDriver;
use IRPayment\Drivers\OnlineDriverDecorator;
use IRPayment\Facades\IRPayment;

class PaymentMethodValueObject
{
    public function __construct(
        public string $paymentMethodDriver
    ) {}

    public function __get(string $name)
    {
        $paymentMethodDriver = $this->paymentMethodDriver;

        $paymentDriver = IRPayment::driver($paymentMethodDriver);

        $normalizedDriver = $this->normalizedDriver($paymentDriver);

        if (! method_exists($normalizedDriver, $name)) {
            throw new BadMethodCallException(sprintf(
                __('The method [%s] does not exist on driver [%s].'),
                $name,
                $paymentMethodDriver
            ));
        }

        return $normalizedDriver->$name();
    }

    /**
     * Get the underlying payment driver from decorated online drivers.
     * 
     * @see \IRPayment\PaymentDriverManager
     * @see \IRPayment\Drivers\OnlineDriverDecorator
     * @see \IRPayment\Contracts\OnlineChannel
     */
    public function normalizedDriver(PaymentDriver|OnlineDriverDecorator $paymentDriver)
    {
        return $paymentDriver instanceof OnlineDriverDecorator ? 
            $paymentDriver->driver : $paymentDriver;
    }

    public function __toString()
    {
        return $this->paymentMethodDriver;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->paymentMethodDriver,
            'title' => $this->title,
            'description' => $this->description,
            'channel' => $this->channel,
        ];
    }
}
