<?php

namespace IRPayment;

use Illuminate\Support\ServiceProvider;

class IRPaymentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'irpayment');
    }
}
