<?php

namespace IRPayment;

use Illuminate\Support\Manager;
use IRPayment\Contracts\Factory;
use IRPayment\Contracts\OnlineChannel;
use IRPayment\Contracts\PaymentDriver;
use IRPayment\Drivers\CardTransfer;
use IRPayment\Drivers\Credit;
use IRPayment\Drivers\OnlineDriverDecorator;
use IRPayment\Drivers\Paykan;
use IRPayment\Drivers\Payping;
use IRPayment\Drivers\Zarinpal;

class PaymentDriverManager extends Manager implements Factory
{
    /**
     * Create a payment driver instance when the configured driver is active.
     *
     * @param  string  $driver
     *
     * @throws \InvalidArgumentException
     *
     * @see \IRPayment\Tests\PaymentDriverDeactiveTest
     */
    protected function createDriver($driver)
    {
        $obj = parent::createDriver($driver);

        // decorate online payment drivers
        // only active drivers can process payment
        if ($obj instanceof OnlineChannel) {
            return new OnlineDriverDecorator($obj);
        }

        return $obj;
    }

    public function getDefaultDriver()
    {
        return $this->config->get('irpayment.default');
    }

    /**
     * @see \IRPayment\Tests\PaymentDriverTest
     * @see \IRPayment\Tests\PaymentDriverImplementionTest
     * @see \IRPayment\Tests\PaymentZarinpalPublicActionsTest
     */
    public function createZarinpalDriver(): PaymentDriver
    {
        $config = collect($this->config->get('irpayment.drivers.zarinpal', []));

        return resolve(Zarinpal::class, ['config' => $config]);
    }

    /**
     * @see \IRPayment\Tests\PaymentDriverTest
     * @see \IRPayment\Tests\PaymentPaypingProcessTest
     */
    public function createPaypingDriver(): PaymentDriver
    {
        $config = collect($this->config->get('irpayment.drivers.payping', []));

        return resolve(Payping::class, ['config' => $config]);
    }

    /**
     * @see \IRPayment\Tests\PaymentDriverTest
     * @see \IRPayment\Tests\PaykanDriverProcessTest
     * @see \IRPayment\Tests\PaykanDriverVerifyTest
     */
    public function createPaykanDriver(): PaymentDriver
    {
        $config = collect($this->config->get('irpayment.drivers.paykan', []));

        return resolve(Paykan::class, ['config' => $config]);
    }

    /**
     * @see \IRPayment\Tests\PaymentDriverTest
     * @see \IRPayment\Tests\PaymentDriverImplementionTest
     */
    public function createCardTransferDriver(): PaymentDriver
    {
        $config = collect($this->config->get('irpayment.drivers.card_transfer', []));

        return resolve(CardTransfer::class, ['config' => $config]);
    }

    /**
     * @see \IRPayment\Tests\PaymentDriverTest
     * @see \IRPayment\Tests\PaymentDriverImplementionTest
     */
    public function createCreditDriver(): PaymentDriver
    {
        $config = collect($this->config->get('irpayment.drivers.credit', []));

        return resolve(Credit::class, ['config' => $config]);
    }
}
