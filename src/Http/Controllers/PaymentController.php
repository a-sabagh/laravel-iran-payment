<?php

namespace IRPayment\Http\Controllers;

use Illuminate\Contracts\View\View;
use IRPayment\Models\Payment;

class PaymentController
{
    public function verify(Payment $payment): View
    {
        return view('irpayment::verify', compact('payment'));
    }
}
