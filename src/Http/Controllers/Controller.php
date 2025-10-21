<?php

namespace IRPayment\Http\Controllers;

use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator as ValidationValidator;
use Illuminate\View\View;
use IRPayment\DTO\VerificationValueObject;
use IRPayment\Enums\PaymentStatus;
use IRPayment\Events\PaymentCanceled;
use IRPayment\Events\PaymentFailed;
use IRPayment\Events\PaymentVerified;
use IRPayment\Models\Payment;

class Controller
{
    /**
     * Handle an invalid verification request. no modification on payment
     *
     * Renders an "invalid" view with validation errors.
     * No Actions on Payment
     */
    protected function handleInvalidRequest(ValidationValidator $validator): View
    {
        $errors = $validator->errors();

        return view('irpayment::invalid', compact('errors'));
    }

    /**
     * Handle a canceled payment without driver verification.
     *
     * Updates the payment status to "CANCELED" and
     * renders the "canceled" view.
     * renders the "invalid" view if payment already completed without modification on payment
     */
    protected function handleCanceledPayment(Payment $payment): View
    {
        if ($payment->status == PaymentStatus::COMPLETE) {
            $errors = new MessageBag([
                'payment' => __('irpayment::messages.payment.already_completed'),
            ]);

            return view('irpayment::invalid', compact('errors'));
        }

        $payment->update(['status' => PaymentStatus::CANCELED]);

        event(new PaymentCanceled($payment));

        return view('irpayment::canceled', compact('payment'));
    }

    /**
     * Handle a failed payment after driver verification attempt.
     *
     * Updates the payment status to "FAILED" along with the error code,
     * and renders the "invalid" view with payment and verification details.
     */
    protected function handleFailedPayment(Payment $payment, VerificationValueObject $verification): View
    {
        $payment->update([
            'code' => $verification->code,
            'status' => PaymentStatus::FAILED,
        ]);

        event(new PaymentFailed($payment, $verification));

        return view('irpayment::invalid', compact('payment', 'verification'));
    }

    /**
     * Handle a successfully verified payment.
     *
     * Updates the payment with verification value object,
     * dispatches the PaymentVerified event,
     * renders the "verify" view with payment and verification data.
     */
    protected function handleVerifiedPayment(Payment $payment, VerificationValueObject $verification): View
    {
        $payment->update($verification->toArray());

        event(new PaymentVerified($payment, $verification));

        return view('irpayment::verify', compact('payment', 'verification'));
    }
}
