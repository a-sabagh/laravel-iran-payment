<?php

namespace IRPayment\Http\Controllers;

use Illuminate\Contracts\View\View;
use IRPayment\Models\Payment;

class PaymentController
{
    public function details(Payment $payment): View
    {
        return view('irpayment::details', compact('payment'));
    }
}
