<?php

namespace IRPayment;

use Illuminate\Support\ServiceProvider;

class IRPaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentDriverManager::class, fn ($app) => new PaymentDriverManager($app));

        $this->mergeConfigFrom(
            __DIR__.'/../config/irpayment.php', 'irpayment'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'irpayment');
    }
}
