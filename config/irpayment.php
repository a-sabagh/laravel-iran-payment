<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This value determines which of the following driver to use.
    | You can switch to a another driver at runtime using strategy design pattern.
    |
    */
    'default' => env('IRPAYMENT_DRIVER_DEFAULT', 'default'),

    /*
    |--------------------------------------------------------------------------
    | List of drivers
    |--------------------------------------------------------------------------
    |
    | These are the list of drivers to use for this package.
    | You can change the name. Then you'll have to change
    | it in the map array too.
    |
    */
    'drivers' => [
        'zarinpal' => [
            'active' => env('IRPAYMENT_ZARINPAL_ACTIVE', 0),
            'merchant_id' => env('IRPAYMENT_ZARINPAL_MERCHANT_ID', ''),
            'currency' => 'IRT',
        ],
        'payping' => [
            'active' => env('IRPAYMENT_PAYPING_ACTIVE', 0),
            'token' => env('IRPAYMENT_PAYPING_TOKEN', ''),
            'currency' => 'IRT',
        ],
        'paykan' => [
            'active' => env('IRPAYMENT_PAYPING_ACTIVE', 0),
            'merchant_id' => env('IRPAYMENT_PAYKAN_MERCHANT_ID', ''),
            'currency' => 'IRT',
        ],
        'card_transfer' => [
            'active' => env('IRPAYMENT_CARD_TRANSFER_ACTIVE', 1),
            'currency' => 'IRT',
        ],
        'credit' => [
            'active' => env('IRPAYMENT_CREDIT_ACTIVE', 1),
            'currency' => 'IRT',
        ],
    ],

];
