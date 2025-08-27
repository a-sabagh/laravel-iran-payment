<?php

namespace IRPayment;

use Illuminate\Support\Manager;
use IRPayment\Contracts\PaymentDriverInterface;
use IRPayment\Drivers\Zarinpal;

class PaymentDriverManager extends Manager
{
    public function getDefaultDriver()
    {
        return $this->config->get('irpayment.default');
    }

    public function createZarinpalDriver(): PaymentDriverInterface
    {
        $config = collect($this->config->get('irpayment.drivers.zarinpal', []));

        return new Zarinpal($config);
    }
}
