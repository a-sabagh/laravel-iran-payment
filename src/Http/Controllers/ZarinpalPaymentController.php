<?php

namespace IRPayment\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use IRPayment\Enums\ZarinpalVerificationStatus;
use IRPayment\Facades\IRPayment;
use IRPayment\Repositories\PaymentRepository;

class ZarinpalPaymentController extends Controller
{
    public function verify(Request $request, PaymentRepository $paymentRepo): View
    {
        $authorityKey = $request->string('authority');
        $payment = $paymentRepo->findByAuthorityKey($authorityKey);

        $validator = Validator::make($request->all(), [
            'authority' => ['required', 'string', 'exists:payments,authority_key'],
            'status' => ['required', Rule::enum(ZarinpalVerificationStatus::class)],
        ]);

        if ($validator->fails()) {
            return $this->handleInvalidRequest($validator);
        }

        $status = $request->enum('status', ZarinpalVerificationStatus::class);

        if ($status == ZarinpalVerificationStatus::CANCELED) {
            return $this->handleCanceledPayment($payment);
        }

        $amount = $payment->amount;

        $verification = IRPayment::driver('zarinpal')
            ->verify($amount, ['authority_key' => $authorityKey]);

        if ($verification->isFailed()) {
            return $this->handleFailedPayment($payment, $verification);
        }

        return $this->handleVerifiedPayment($payment, $verification);
    }
}
