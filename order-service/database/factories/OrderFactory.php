<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\{Order, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status'  => OrderStatus::PENDING,
            'total'   => fake()->randomFloat(2, 10, 5000),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(['status' => OrderStatus::CONFIRMED]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => OrderStatus::CANCELLED]);
    }
}
