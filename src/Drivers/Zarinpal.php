<?php

namespace IRPayment\Drivers;

use Illuminate\Support\Collection;
use IRPayment\Contracts\PaymentDriver;

class Zarinpal implements PaymentDriver
{
    public function __construct(
        protected Collection $config
    ) {}
}
