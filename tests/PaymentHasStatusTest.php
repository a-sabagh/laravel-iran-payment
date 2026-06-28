<?php

namespace IRPayment\Tests;

use IRPayment\Enums\PaymentStatus;
use IRPayment\Models\Payment;

class PaymentHasStatusTest extends TestCase
{
    public function test_payment_has_status_accepts_payment_status_enum(): void
    {
        $payment = new Payment([
            'status' => PaymentStatus::PROCESSING,
        ]);

        $this->assertTrue($payment->hasStatus(PaymentStatus::PROCESSING));
        $this->assertFalse($payment->hasStatus(PaymentStatus::COMPLETE));
    }

    public function test_payment_has_status_accepts_status_string(): void
    {
        $payment = new Payment([
            'status' => PaymentStatus::COMPLETE,
        ]);

        $this->assertTrue($payment->completed);

        $this->assertTrue($payment->hasStatus('complete'));
        $this->assertFalse($payment->hasStatus('failed'));
    }

    public function test_payment_has_status_accepts_status_array(): void
    {
        $payment = new Payment([
            'status' => PaymentStatus::FAILED,
        ]);

        $this->assertTrue($payment->hasStatus([
            PaymentStatus::PENDING,
            'failed',
        ]));

        $this->assertFalse($payment->hasStatus([
            PaymentStatus::PENDING,
            PaymentStatus::COMPLETE,
        ]));
    }

    public function test_payment_has_status_ignores_invalid_status_strings(): void
    {
        $payment = new Payment([
            'status' => PaymentStatus::CANCELED,
        ]);

        $this->assertFalse($payment->hasStatus('invalid'));
        $this->assertTrue($payment->hasStatus([
            'invalid',
            PaymentStatus::CANCELED,
        ]));
    }

    public function test_payment_missing_status_returns_inverse_of_has_status(): void
    {
        $payment = new Payment([
            'status' => PaymentStatus::PENDING,
        ]);

        $this->assertFalse($payment->missingStatus(PaymentStatus::PENDING));
        $this->assertTrue($payment->missingStatus(PaymentStatus::COMPLETE));
        $this->assertFalse($payment->missingStatus([
            PaymentStatus::PROCESSING,
            'pending',
        ]));
    }
}
