<?php

namespace IRPayment;

use IRPayment\Enums\PaymentChannel;

/** @see \IRPayment\Tests\HelpersTest */
function get_available_payment_drivers(): array
{
    return collect(config('irpayment.drivers'))
        ->reject(fn ($driver) => ! $driver['active'])
        ->reject(fn ($driver) => $driver['channel'] == PaymentChannel::OFFLINE)
        ->keys()
        ->toArray();
}
