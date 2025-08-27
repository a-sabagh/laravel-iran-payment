<?php

namespace IRPayment\Contracts;

interface PaymentDriver
{
    public function process();

    public function verify();
}
