<?php

namespace IRPayment;

use Illuminate\Support\ServiceProvider;

class IRPaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/irpayment.php', 'irpayment'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'irpayment');
    }
}
