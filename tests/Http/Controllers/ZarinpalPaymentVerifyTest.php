<?php

namespace IRPayment\Tests\Http\Controllers;

use Illuminate\Validation\ValidationException;
use IRPayment\Tests\TestCase;

class ZarinpalPaymentVerifyTest extends TestCase
{
    public function test_payment_verification_status_invalid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The selected status is invalid.');

        $this->withoutExceptionHandling();
        $authorityKey = fake()->unique()->regexify('A00000[A-Z0-9a-z]{32,40}');

        $requestData = [
            'authority' => $authorityKey,
            'status' => 'invalid-zarinpal-status',
        ];

        $this->get(route('irpayment.payment.zarinpal.verify', $requestData));
    }
}
