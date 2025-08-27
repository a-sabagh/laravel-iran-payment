<?php

namespace IRPayment\Facades;

use Illuminate\Support\Facades\Facade;
use IRPayment\PaymentDriverManager;

class IRPayment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PaymentDriverManager::class;
    }
}