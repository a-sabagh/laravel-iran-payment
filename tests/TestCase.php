<?php

namespace IRPayment\Tests;

use IRPayment\IRPaymentServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class TestCase extends TestbenchTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('irpayment.drivers.paykan.active', 1);
        config()->set('irpayment.drivers.payping.active', 1);
        config()->set('irpayment.drivers.zarinpal.active', 1);
        config()->set('irpayment.drivers.card_transfer.active', 1);
        config()->set('irpayment.drivers.credit.active', 1);
    }

    protected function getPackageProviders($app)
    {
        return [
            IRPaymentServiceProvider::class,
        ];
    }
}
