<?php

namespace IRPayment\Events;

use Illuminate\Foundation\Bus\Dispatchable;
use IRPayment\Models\Payment;

class PaymentCancelled
{
    use Dispatchable;

    public function __construct(
        public Payment $payment,
    ) {}
}
