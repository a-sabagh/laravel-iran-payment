<?php

namespace IRPayment\Drivers;

use Illuminate\Support\Collection;
use IRPayment\Contracts\PaymentDriverInterface;

class Zarinpal implements PaymentDriverInterface
{
    public function __construct(
        protected Collection $config
    ) {}
}
