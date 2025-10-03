<?php

use IRPayment\Enums\PaymentChannel;
use IRPayment\Enums\PaymentStatus;

return [

    PaymentChannel::class => [
        'online' => 'آنلاین',
        'offline' => 'آفلاین',
    ],

    PaymentStatus::class => [
        'pending' => 'در انتظار',
        'processing' => 'در حال پردازش',
        'complete' => 'تکمیل شده',
        'failed' => 'ناموفق',
        'canceled' => 'لغو شده',
    ],
];
