<?php

namespace IRPayment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use IRPayment\Models\Payment;

class PaymentVerified
{
    use Dispatchable;

    public function __construct(
        public Payment $payment
    ) {}
}
