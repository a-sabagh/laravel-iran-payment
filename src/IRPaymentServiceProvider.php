<?php

namespace IRPayment;

use Illuminate\Support\Facades\Route;
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
        $this->registerRoutes();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'irpayment');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'irpayment');

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/irpayment'),
            __DIR__.'/../config/irpayment.php' => config_path('irpayment.php'),
            __DIR__.'/../resources/views' => resource_path('views/vendor/irpayment'),
        ], 'irpayment');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);
    }

    protected function registerRoutes()
    {
        Route::group(['as' => 'irpayment.'], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }
}
