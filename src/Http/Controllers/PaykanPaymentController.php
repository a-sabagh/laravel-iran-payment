<?php

namespace IRPayment\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use IRPayment\Facades\IRPayment;
use IRPayment\Repositories\PaymentRepository;

class PaykanPaymentController extends Controller
{
    /** @see \IRPayment\Tests\Http\Controllers\PaykanPaymentVerifyTest */
    public function verify(Request $request, PaymentRepository $paymentRepo): View
    {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required'],
            'ref_num' => ['required', 'string', 'exists:payments,authority_key'],
            'tracking_code' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->handleInvalidRequest($validator);
        }

        // authority key same as reference number
        $authorityKey = $request->string('ref_num');

        $payment = $paymentRepo->findByAuthorityKey($authorityKey);

        $credentials = [
            'order_id' => $request->input('order_id'),
            'tracking_code' => $request->input('tracking_code'),
            'ref_num' => $authorityKey,
        ];

        $verification = IRPayment::driver('paykan')
            ->verify($payment->amount, $credentials);

        if ($verification->isFailed()) {
            return $this->handleFailedPayment($payment, $verification);
        }

        if ($verification->isAlreadyVerified()) {
            return $this->handleAlreadyVerifiedPayment($payment, $verification);
        }

        return $this->handleVerifiedPayment($payment, $verification);
    }
}
