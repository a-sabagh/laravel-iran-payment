<?php

namespace IRPayment\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use IRPayment\Enums\PaymentStatus;
use IRPayment\Enums\ZarinpalVerificationStatus;
use IRPayment\Events\PaymentVerified;
use IRPayment\Facades\IRPayment;
use IRPayment\Repositories\PaymentRepository;

class ZarinpalPaymentController
{
    public function verify(Request $request, PaymentRepository $paymentRepo): View
    {
        $authorityKey = $request->string('authority');
        $payment = $paymentRepo->findByAuthorityKey($authorityKey);

        $validator = Validator::make($request->all(), [
            'authority' => ['required', 'string', 'exists:payments,authority_key'],
            'status' => ['required', Rule::enum(ZarinpalVerificationStatus::class)],
        ]);

        // payment failed on invalidation callback request
        if ($validator->fails()) {
            $errors = $validator->errors();

            return view('irpayment::invalid', compact('errors'));
        }

        $status = $request->enum('status', ZarinpalVerificationStatus::class);

        // payment cancelled on callback request status
        if ($status == ZarinpalVerificationStatus::CANCELED) {
            $payment->update(['status' => PaymentStatus::CANCELED]);

            return view('irpayment::cancelled', compact('payment'));
        }

        $amount = $payment->amount;

        $verification = IRPayment::driver('zarinpal')
            ->verify($amount, $authorityKey);

        // payment failed on driver verify request response
        if ($verification->isFailed()) {
            $payment->update([
                'code' => $verification->code,
                'status' => PaymentStatus::FAILED,
            ]);

            return view('irpayment::invalid', compact('payment', 'verification'));
        }

        // payment verified
        $payment->update($verification->toArray());

        event(new PaymentVerified($payment));

        return view('irpayment::verify', compact('payment', 'verification'));
    }
}
