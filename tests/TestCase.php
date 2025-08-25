<?php

namespace IRPayment\Tests;

use IRPayment\IRPaymentServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            IRPaymentServiceProvider::class,
        ];
    }
}
