<?php

namespace IRPayment;

use Illuminate\Support\Manager;
use IRPayment\Contracts\Factory;
use IRPayment\Contracts\PaymentDriver;
use IRPayment\Drivers\CardTransfer;
use IRPayment\Drivers\Payping;
use IRPayment\Drivers\Zarinpal;

class PaymentDriverManager extends Manager implements Factory
{
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

    public function createCardTransferDriver(): PaymentDriver
    {
        $config = collect($this->config->get('irpayment.drivers.card_payment', []));

        return new CardTransfer(
            $config
        );
    }
}
