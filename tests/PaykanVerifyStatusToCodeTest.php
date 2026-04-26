<?php

namespace IRPayment\Tests;

use Illuminate\Support\Collection;
use IRPayment\Drivers\Paykan;
use ReflectionMethod;

class PaykanVerifyStatusToCodeTest extends TestCase
{
    public function test_paykan_verify_status_to_code_mapping(): void
    {
        $driver = new Paykan(new Collection);

        $method = new ReflectionMethod($driver, 'toCode');

        $method->setAccessible(true);

        $this->assertSame(200, $method->invoke($driver, 'CONFIRMED'));
        $this->assertSame(508, $method->invoke($driver, 'FAILED'));
        $this->assertSame(400, $method->invoke($driver, 'INVALID_CARD'));
        $this->assertSame(503, $method->invoke($driver, 'CANCELLED'));
    }
}
