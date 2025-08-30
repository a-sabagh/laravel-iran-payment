<?php

namespace IRPayment\Tests\Http\Controllers;

use IRPayment\Tests\TestCase;

class ZarinpalPaymentVerifyTest extends TestCase
{
    public function test_payment_verification_status_invalid(): void
    {
        $authorityKey = fake()->unique()->regexify('A00000[A-Z0-9a-z]{32,40}');

        $requestData = [
            'authority' => $authorityKey,
            'status' => 'invalid-zarinpal-status',
        ];

        $response = $this->get(route('irpayment.payment.zarinpal.verify'), $requestData);

        $response->assertInvalid('status');
    }
}
