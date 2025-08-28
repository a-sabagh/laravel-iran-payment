<?php

namespace IRPayment\Http\Controllers;

use Illuminate\Contracts\View\View;
use IRPayment\Facades\IRPayment;
use IRPayment\Models\Payment;

class PaymentController
{
    public function verify(Payment $payment): View
    {
        $paymentMethod = $payment->payment_method;

        $verify = IRPayment::driver($paymentMethod)
            ->verify($payment);

        return view('irpayment::verify', compact('payment'));
    }
}
