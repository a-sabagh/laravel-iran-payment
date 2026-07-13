<?php

namespace IRPayment\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use IRPayment\Exceptions\PaymentDriverException;
use IRPayment\Facades\IRPayment;
use IRPayment\Models\Payment;
use PHPUnit\Framework\Attributes\DataProvider;
use Workbench\App\Models\Order;

use function Orchestra\Testbench\workbench_path;

/** @see \IRPayment\Drivers\Paykan::process */
class PaykanDriverProcessHttpErrorTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    public static function paykanHttpErrorProvider(): array
    {
        $messages = require __DIR__.'/../lang/fa_IR/messages.php';

        return collect($messages['paykan']['http'])
            ->reject(fn (string $message, int|string $statusCode): bool => (int) $statusCode === 200)
            ->map(fn (string $message, int|string $statusCode): array => [(int) $statusCode, $message])
            ->values()
            ->all();
    }

    #[DataProvider('paykanHttpErrorProvider')]
    public function test_process_translates_paykan_http_error_responses(int $statusCode, string $message): void
    {
        $this->app->setLocale('fa_IR');

        $this->expectException(PaymentDriverException::class);
        $this->expectExceptionCode($statusCode);
        $this->expectExceptionMessage($message);

        $order = Order::factory()->create();

        $payment = Payment::factory()
            ->for($order, 'paymentable')
            ->create();

        $this->app->config->set('irpayment.drivers.paykan', [
            'active' => true,
            'merchant_id' => fake()->numerify('merchant-####'),
            'currency' => 'IRR',
        ]);

        Http::fake([
            'https://pgw.paykan.app/api/v1/withdraw/' => Http::response([], $statusCode),
        ]);

        IRPayment::driver('paykan')->process($payment);
    }
}
