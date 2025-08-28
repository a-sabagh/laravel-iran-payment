<?php

namespace IRPayment\Contracts;

use IRPayment\Models\Payment;

interface PaymentDriver
{
    public function process(Payment $payment);

    public function verify(Payment $payment);
}
