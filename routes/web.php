<?php

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use IRPayment\Http\Controllers\PaymentController;
use IRPayment\Http\Controllers\ZarinpalPaymentController;

Route::get('payment/zarinpal/verify', [ZarinpalPaymentController::class, 'verify'])
    ->name('payment.zarinpal.verify');

Route::get('payment/details/{payment}', [PaymentController::class, 'details'])
    ->name('payment.details')
    ->middleware([SubstituteBindings::class]);
