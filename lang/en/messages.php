<?php

return [
    'zarinpal' => [
        -9  => 'Validation error',
        -10 => 'Terminal is not valid, please check merchant_id or ip address.',
        -11 => 'Terminal is not active, please contact our support team.',
        -12 => 'Too many attempts, please try again later.',
        -15 => 'Terminal user is suspend : (please contact our support team).',
        -16 => 'Terminal user level is not valid : ( please contact our support team).',
        -17 => 'Terminal user level is not valid : ( please contact our support team).',
        -18 => 'The referrer address does not match the registered domain.',
        -19 => 'Terminal user transactions are banned.',
        100 => 'Success',

        // PaymentRequest
        -30 => 'Terminal do not allow to accept floating wages.',
        -31 => 'Terminal do not allow to accept wages, please add default bank account in panel.',
        -32 => 'Wages is not valid, Total wages(floating) has been overload max amount.',
        -33 => 'Wages floating is not valid.',
        -34 => 'Wages is not valid, Total wages(fixed) has been overload max amount.',
        -35 => 'Wages is not valid, Total wages(floating) has been reached the limit in max parts.',
        -36 => 'The minimum amount for wages(floating) should be 10,000 Rials',
        -37 => 'One or more iban entered for wages(floating) from the bank side are inactive.',
        -38 => 'Wages need to set Iban in shaparak.',
        -39 => 'Wages have a error!',
        -40 => 'Invalid extra params, expire_in is not valid.',
        -41 => 'Maximum amount is 100,000,000 tomans.',

        // PaymentVerify
        -50 => 'Session is not valid, amounts values is not the same.',
        -51 => 'Session is not valid, session is not active paid try.',
        -52 => 'Oops!!, please contact our support team.',
        -53 => 'Session is not this merchant_id session.',
        -54 => 'Invalid authority.',
        -55 => 'manual payment request not found.',
        101 => 'Verified',

        // PaymentReverse
        -60 => 'Session can not be reversed with bank.',
        -61 => 'Session is not in success status.',
    ],
];
