<?php

namespace IRPayment;

use Illuminate\Support\Manager;
use IRPayment\Contracts\Factory;
use IRPayment\Contracts\PaymentDriver;
use IRPayment\Drivers\CardTransfer;
use IRPayment\Drivers\Paykan;
use IRPayment\Drivers\Payping;
use IRPayment\Drivers\Zarinpal;
use IRPayment\Exceptions\PaymentDriverNotActive;

class PaymentDriverManager extends Manager implements Factory
{
    /**
     * Create a payment driver instance when the configured driver is active.
     *
     * @param  string  $driver
     * @return mixed
     *
     * @throws \IRPayment\Exceptions\PaymentDriverNotActive
     * @throws \InvalidArgumentException
     * 
     * @see \IRPayment\Tests\PaymentDriverDeactiveTest
     */
    protected function createDriver($driver)
    {
        $obj = parent::createDriver($driver);

        if (! $obj->config->get('active')) {
            throw new PaymentDriverNotActive("Driver {$driver} is deactive");
        }

        return $obj;
    }

    public function getDefaultDriver()
    {
        return $this->config->get('irpayment.default');
    }

    public function createZarinpalDriver(): PaymentDriver
    {
        $config = collect($this->config->get('irpayment.drivers.zarinpal', []));

        return new Zarinpal(
            $config
        );
    }

    public function createPaypingDriver(): PaymentDriver
    {
        $config = collect($this->config->get('irpayment.drivers.payping', []));

        return new Payping(
            $config
        );
    }

    public function createPaykanDriver(): PaymentDriver
    {
        $config = collect($this->config->get('irpayment.drivers.paykan', []));

        return new Paykan(
            $config
        );
    }

    public function createCardTransferDriver(): PaymentDriver
    {
        $config = collect($this->config->get('irpayment.drivers.card_transfer', []));

        return new CardTransfer(
            $config
        );
    }
}
