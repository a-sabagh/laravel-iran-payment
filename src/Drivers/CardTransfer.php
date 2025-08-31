<?php

namespace IRPayment\Drivers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use IRPayment\Contracts\PaymentDriver;
use IRPayment\Enums\PaymentChannel;

class CardTransfer implements PaymentDriver
{
    public function __construct(
        protected Collection $config
    ) {}

    public function title(): string
    {
        return $this->config->get('title', Lang::get('irpayment::drivers.card_transfer.title'));
    }

    public function channel(): PaymentChannel
    {
        return PaymentChannel::OFFLINE;
    }

    public function description(): string
    {
        return $this->config->get('description', Lang::get('irpayment::drivers.card_transfer.description'));
    }
}
