<?php

namespace IRPayment\Http\Controllers;

use Illuminate\Contracts\View\View;
use IRPayment\Models\Payment;

class PaymentController
{
    /** @see \IRPayment\Tests\Http\Controllers\PaymentControllerTest */
    public function details(Payment $payment): View
    {
        return view('irpayment::details', compact('payment'));
    }
}
