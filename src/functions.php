<?php

namespace IRPayment;

use IRPayment\Enums\PaymentChannel;

function get_available_payment_drivers(): array
{
    return collect(config('irpayment.drivers'))
        ->reject(fn ($driver) => $driver['channel'] == PaymentChannel::OFFLINE)
        ->keys()
        ->toArray();
}
