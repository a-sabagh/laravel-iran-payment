<?php

namespace IRPayment\Contracts;

use IRPayment\Models\Payment;

interface PaymentDriver
{
    public function title(): string;

    public function description(): string;

    public function process(Payment $payment);

    public function verify(int $amount, string $authorityKey);
}
