<?php

namespace IRPayment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use IRPayment\Contracts\PaymentDriver;

class Zarinpal implements PaymentDriver
{
    public function __construct(
        protected Request $request,
        protected Collection $config
    ) {}

    public function process() {}

    protected function request() {}

    protected function startPay() {}

    public function verify() {}
}
