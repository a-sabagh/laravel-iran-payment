<?php

namespace IRPayment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use IRPayment\Facades\IRPayment;
use IRPayment\Repositories\PaymentRepository;

class PaypingPaymentController extends Controller
{
    public function verify(Request $request, PaymentRepository $paymentRepo)
    {
        $encodedData = $request->string('data');

        $request->merge([
            'data' => json_decode($encodedData, true),
        ]);

        $validator = Validator::make($request->all(), [
            'data' => ['required', 'array'],
            'data.paymentCode' => ['required', 'string'],
            'data.clientRefId' => ['required', 'numeric', 'exists:payments,id'],
            'status' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->handleInvalidRequest($validator);
        }

        $authorityKey = $paymentCode = $request->integer('data.paymentCode');
        $payment = $paymentRepo->findByAuthorityKey($authorityKey);

        if (! (bool) $request->status) {
            return $this->handleCanceledPayment($payment);
        }

        $amount = $payment->amount;
        $clientRefId = $request->integer('data.clientRefId');

        $verification = IRPayment::driver('payping')
            ->verify($amount, $clientRefId, $paymentCode);

        if ($verification->isFailed()) {
            return $this->handleFailedPayment($payment, $verification);
        }

        if ($verification->isAlreadyVerified()) {
            return $this->handleAlreadyVerifiedPayment($payment, $verification);
        }

        return $this->handleVerifiedPayment($payment, $verification);
    }
}
