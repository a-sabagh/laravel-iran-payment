<?php

namespace IRPayment\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use IRPayment\Enums\ZarinpalVerificationStatus;
use IRPayment\Facades\IRPayment;
use IRPayment\Repositories\PaymentRepository;

class ZarinpalPaymentController
{
    public function verify(Request $request, PaymentRepository $paymentRepo): View
    {
        $validator = Validator::make($request->all(), [
            'authority' => ['required', 'string', 'exists:payments,authority_key'],
            'status' => ['required', Rule::enum(ZarinpalVerificationStatus::class)],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return view('irpayment::invalid', compact('errors'));
        }

        $status = $request->enum('status', ZarinpalVerificationStatus::class);

        if ($status == ZarinpalVerificationStatus::CANCELED) {
            return view('irpayment::cancelled', compact('payment'));
        }

        $authorityKey = $request->string('authority');
        $payment = $paymentRepo->findByAuthorityKey($authorityKey);
        $amount = $payment->amount;

        $verification = IRPayment::driver('zarinpal')
            ->verify($amount, $authorityKey);

        if ($verification->isFailed()) {
            return view('irpayment::invalid', compact('payment'));
        }

        $payment->update($verification->toArray());

        return view('irpayment::verify', compact('payment'));
    }
}
