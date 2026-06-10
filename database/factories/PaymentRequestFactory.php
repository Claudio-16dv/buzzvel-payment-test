<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentRequest>
 */
class PaymentRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rates = [
            'EUR' => 1.0,
            'USD' => 1.08,
            'BRL' => 5.98,
            'GBP' => 0.85,
            'JPY' => 168.5,
            'CAD' => 1.47,
        ];

        $currency = fake()->randomElement(array_keys($rates));
        $rate = $rates[$currency];
        $amount = fake()->randomFloat(2, 50, 5000);

        return [
            'user_id' => User::factory(),
            'amount' => $amount,
            'currency' => $currency,
            'exchange_rate' => $rate,
            'amount_in_eur' => round($amount / $rate, 2),
            'rate_source' => 'exchangerate-api.com',
            'rate_fetched_at' => now(),
            'status' => PaymentStatus::Pending,
            'description' => fake()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => PaymentStatus::Pending]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::Rejected,
            'reviewed_at' => now(),
        ]);
    }
}
