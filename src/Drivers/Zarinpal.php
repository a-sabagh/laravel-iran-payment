<?php

namespace IRPayment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use IRPayment\Contracts\PaymentDriver;
use IRPayment\Models\Payment;

class Zarinpal implements PaymentDriver
{
    public function __construct(
        protected Request $request,
        protected Collection $config
    ) {}

    public function title(): string
    {
        return $this->config->get('title', Lang::get('irpayment::drivers.zarinpal'));
    }

    public function description(): string
    {
        return $this->config->get('description', Lang::get('irpayment::drivers.zarinpal'));
    }

    public function process(Payment $payment): void {}

    protected function request() {}

    protected function startPay() {}

    public function verify(Payment $payment) {}
}
