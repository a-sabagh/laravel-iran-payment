<?php

use Illuminate\Support\Facades\Route;
use IRPayment\Http\Controllers\PaymentController;

Route::get('payment/verify/{authorityKey}', [PaymentController::class, 'verify']);
