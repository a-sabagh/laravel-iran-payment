<?php

namespace IRPayment\Drivers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use IRPayment\Contracts\PaymentDriver;
use IRPayment\Enums\PaymentChannel;

class Credit implements PaymentDriver
{
    public function __construct(
        public Collection $config
    ) {}

    public function title(): string
    {
        return $this->config->get('title', Lang::get('irpayment::drivers.credit.title'));
    }

    public function channel(): PaymentChannel
    {
        return PaymentChannel::OFFLINE;
    }

    public function description(): string
    {
        return $this->config->get('description', Lang::get('irpayment::drivers.credit.description'));
    }
}
