<?php

namespace IRPayment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use IRPayment\Enums\PaymentChannel;
use IRPayment\Models\Payment;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'code' => fake()->numberBetween(100, 999),
            'payment_channel' => fake()->randomElement(array_column(PaymentChannel::cases(), 'value')),
            'payment_method' => null,
            'message' => fake()->sentence(),
            'card_hash' => fake()->sha256(),
            'card_mask' => fake()->numerify('****-****-****-####'),
            'reference_id' => fake()->unique()->numerify('####################'),
            'amount' => fake()->numberBetween(1000, 1000000),
            'metadata' => [
                'ip' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
            ],
        ];
    }

    public function offline(): static
    {
        return $this->state([
            'payment_channel' => PaymentChannel::OFFLINE,
            'payment_method' => null,
        ]);
    }
}
