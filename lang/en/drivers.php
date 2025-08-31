<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Drivers (English)
    |--------------------------------------------------------------------------
    | You can also configure the driver in your configuration files,
    | However, this is not recommended because it never changes based on your application locale.
    | The array keys below refer to the corresponding payment drivers.
    */

    'card_transfer' => [
        'title' => 'Card-to-Card Transfer',
        'description' => 'Pay offline by transferring money directly from your card to the merchant’s card.',
    ],

    'zarinpal' => [
        'title' => 'Zarinpal Payment Gateway',
        'description' => 'Pay securely online via the Zarinpal payment gateway.',
    ],

];
