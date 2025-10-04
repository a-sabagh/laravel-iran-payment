<?php

namespace IRPayment\DTO;

use BadMethodCallException;
use IRPayment\Facades\IRPayment;

class PaymentMethodValueObject
{
    public function __construct(
        public string $paymentMethodDriver
    ) {}

    public function __get($name)
    {
        $paymentMethodDriver = $this->paymentMethodDriver;

        $paymentDriver = IRPayment::driver($paymentMethodDriver);

        if (! method_exists($paymentDriver, $name)) {
            throw new BadMethodCallException(sprintf(
                __('The method [%s] does not exist on driver [%s].'),
                $name,
                $paymentMethodDriver
            ));
        }

        return $paymentDriver->$name();
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
