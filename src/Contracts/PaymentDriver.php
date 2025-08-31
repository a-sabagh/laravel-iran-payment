<?php

namespace IRPayment\Contracts;

use IRPayment\Enums\PaymentChannel;

interface PaymentDriver
{
    public function title(): string;

    public function description(): string;

    public function channel(): PaymentChannel;
}
