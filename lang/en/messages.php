<?php

return [
    'zarinpal' => [
        -9 => 'Validation error',
        -10 => 'Terminal is not valid, please check merchant_id or ip address.',
        -11 => 'Terminal is not active, please contact our support team.',
        -12 => 'Too many attempts, please try again later.',
        -15 => 'Terminal user is suspended (please contact our support team).',
        -16 => 'Terminal user level is not valid (please contact our support team).',
        -17 => 'Terminal user limited in Blue level.',
        -18 => 'The referrer address does not match the registered domain.',
        -19 => 'Terminal user transactions are banned.',
        100 => 'Success',

        // PaymentRequest
        -30 => 'Terminal does not allow to accept floating wages.',
        -31 => 'Add default bank account in panel. Wages data is invalid.',
        -32 => 'Wages is not valid, total wages (floating) exceeded max amount.',
        -33 => 'Wages floating is not valid.',
        -34 => 'Wages is not valid, total wages (fixed) exceeded max amount.',
        -35 => 'Wages is not valid, too many receivers.',
        -36 => 'The minimum amount for wages(floating) should be 10,000 Rials',
        -37 => 'One or more IBAN entered for wages are inactive.',
        -38 => 'IBAN definition error in Shaparak, please retry later.',
        -39 => 'Wages error occurred, please contact support.',
        -40 => 'Invalid extra params, expire_in is not valid.',
        -41 => 'Maximum amount is 100,000,000 tomans.',

        // PaymentVerify
        -50 => 'Session is not valid, amount mismatch.',
        -51 => 'Payment failed.',
        -52 => 'Unexpected error, please contact support.',
        -53 => 'Payment does not belong to this merchant_id.',
        -54 => 'Invalid authority.',
        -55 => 'Manual payment request not found.',
        101 => 'Transaction already verified.',

        // PaymentReverse
        -60 => 'Session cannot be reversed with bank.',
        -61 => 'Transaction not successful or already reversed.',
        -62 => 'Terminal IP is not set.',
        -63 => 'Reverse window expired (30 minutes).',
    ],
];
