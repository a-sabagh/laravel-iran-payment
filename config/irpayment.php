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
    'default' => env('IRPYMENT_DRIVER_DEFAULT', 'default'),

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
            'merchant_id' => env('IRPYMENT_ZARINPAL_MERCHANT_ID', ''),
            'currency' => 'IRT',
        ],
        'payping' => [
            'token' => env('IRPYMENT_PAYPING_TOKEN', ''),
            'currency' => 'IRT',
        ],
    ],

];
