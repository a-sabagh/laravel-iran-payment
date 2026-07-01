<?php

namespace IRPayment;

use IRPayment\Enums\PaymentChannel;
use IRPayment\Facades\IRPayment;

/** @see \IRPayment\Tests\HelpersAvailablePaymentDriversTest */
function get_available_payment_drivers(): array
{
    return collect(config('irpayment.drivers'))
        ->reject(fn ($driver) => ! data_get($driver, 'active'))
        ->reject(fn ($driver) => data_get($driver, 'channel') == PaymentChannel::OFFLINE)
        ->keys()
        ->toArray();
}

/** @see \IRPayment\Tests\HelpersActivePaymentDriversTest */
function get_active_payment_drivers(): array
{
    return collect(config('irpayment.drivers'))
        ->reject(fn ($driver) => ! data_get($driver, 'active'))
        ->keys()
        ->toArray();
}

/** @see \IRPayment\Tests\HelpersDeactivePaymentDriversTest */
function get_deactive_payment_drivers(): array
{
    return collect(config('irpayment.drivers'))
        ->reject(fn ($driver) => data_get($driver, 'active'))
        ->keys()
        ->toArray();
}

/** @see \IRPayment\Tests\HelpersActivePaymentDriversEnhancedTest */
function get_active_payment_drivers_enhance_options(): array
{
    $results = [];

    $drivers = get_active_payment_drivers();

    if (! empty($drivers)) {
        foreach ($drivers as $driver) {
            $results[] = [
                'id' => $driver,
                'text' => IRPayment::driver($driver)->title(),
                'caption' => IRPayment::driver($driver)->description(),
            ];
        }
    }

    return $results;
}
