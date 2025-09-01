<?php

namespace IRPayment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use IRPayment\DTO\VerificationValueObject;
use IRPayment\Models\Payment;

class PaymentVerified
{
    use Dispatchable;

    public function __construct(
        public Payment $payment,
        public VerificationValueObject $verification
    ) {}
}
