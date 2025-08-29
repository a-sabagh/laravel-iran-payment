<?php

namespace IRPayment\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use IRPayment\Enums\ZarinpalVerificationStatus;
use IRPayment\Facades\IRPayment;
use IRPayment\Repositories\PaymentRepository;

class ZarinpalPaymentController
{
    public function verify(Request $request, PaymentRepository $paymentRepo): View
    {
        $request->validate([
            'authority' => ['required', 'string'],
            'status' => ['required', Rule::enum(ZarinpalVerificationStatus::class)],
        ]);

        $authorityKey = $request->string('authority');

        $payment = $paymentRepo->findByAuthorityKey($authorityKey);

        if (! $payment) {
            return view('irpayment::invalid');
        }

        $status = $request->enum('status', ZarinpalVerificationStatus::class);

        if ($status == ZarinpalVerificationStatus::CANCELED) {
            return view('irpayment::cancelled', compact('payment'));
        }

        $amount = $payment->amount;

        $verification = IRPayment::driver('zarinpal')
            ->verify($authorityKey, $amount);

        if ($verification->isFailed()) {
            return view('irpayment::invalid', compact('payment'));
        }

        $payment->update($verification->toArray());

        return view('irpayment::verify', compact('payment'));
    }
}
