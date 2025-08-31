<?php

namespace IRPayment\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;
use IRPayment\DTO\VerificationValueObject;
use IRPayment\Enums\PaymentStatus;
use IRPayment\Enums\ZarinpalVerificationStatus;
use IRPayment\Events\PaymentVerified;
use IRPayment\Facades\IRPayment;
use IRPayment\Models\Payment;
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

        if ($validator->fails()) {
            return $this->handleInvalidRequest($validator);
        }

        $status = $request->enum('status', ZarinpalVerificationStatus::class);

        if ($status == ZarinpalVerificationStatus::CANCELED) {
            return $this->handleCancelledPayment($payment);
        }

        $amount = $payment->amount;

        $verification = IRPayment::driver('zarinpal')
            ->verify($amount, $authorityKey);

        if ($verification->isFailed()) {
            return $this->handleFailedPayment($payment, $verification);
        }

        return $this->handleVerifiedPayment($payment, $verification);
    }

    /**
     * Handle an invalid verification request. no modification on payment
     *
     * Renders an "invalid" view with validation errors.
     * No Actions on Payment
     */
    private function handleInvalidRequest(ValidationValidator $validator): View
    {
        $errors = $validator->errors();

        return view('irpayment::invalid', compact('errors'));
    }

    /**
     * Handle a cancelled payment without driver verification.
     *
     * Updates the payment status to "CANCELED" and
     * renders the "cancelled" view.
     * renders the "invalid" view if payment already completed without modification on payment
     */
    private function handleCancelledPayment(Payment $payment): View
    {
        if ($payment->status == PaymentStatus::COMPLETE) {
            $errors = new MessageBag([
                'payment' => __('irpayment::messages.payment.already_completed'),
            ]);

            return view('irpayment::invalid', compact('errors'));
        }

        $payment->update(['status' => PaymentStatus::CANCELED]);

        return view('irpayment::cancelled', compact('payment'));
    }

    /**
     * Handle a failed payment after driver verification attempt.
     *
     * Updates the payment status to "FAILED" along with the error code,
     * and renders the "invalid" view with payment and verification details.
     */
    private function handleFailedPayment(Payment $payment, VerificationValueObject $verification): View
    {
        $payment->update([
            'code' => $verification->code,
            'status' => PaymentStatus::FAILED,
        ]);

        return view('irpayment::invalid', compact('payment', 'verification'));
    }

    /**
     * Handle a successfully verified payment.
     *
     * Updates the payment with verification value object,
     * dispatches the PaymentVerified event,
     * renders the "verify" view with payment and verification data.
     */
    private function handleVerifiedPayment(Payment $payment, VerificationValueObject $verification): View
    {
        $payment->update($verification->toArray());

        event(new PaymentVerified($payment));

        return view('irpayment::verify', compact('payment', 'verification'));
    }
}
