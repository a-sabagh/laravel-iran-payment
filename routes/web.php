<?php

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use IRPayment\Http\Controllers\PaymentController;

Route::get('payment/verify/{payment}', [PaymentController::class, 'verify'])
    ->name('payment.verify')
    ->middleware([SubstituteBindings::class]);
