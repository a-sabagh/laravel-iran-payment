<?php

namespace IRPayment\Contracts;

use IRPayment\Models\Payment;

interface OnlineChannel
{
    public function CallbackUrl(): string;

    public function process(Payment $payment);

    public function verify(int $amount, string $authorityKey);
}
