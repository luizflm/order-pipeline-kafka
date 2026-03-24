<?php

namespace Database\Factories;

use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id'         => fake()->unique()->numberBetween(1, 10000),
            'customer_name'    => fake()->name(),
            'shipping_address' => fake()->streetAddress(),
            'city'             => fake()->city(),
            'country'          => fake()->country(),
            'status'           => ShipmentStatus::PENDING,
            'tracking_code'    => strtoupper('TRK-' . now()->format('Ymd') . '-' . str()->random(6)),
            'estimated_at'     => fake()->dateTimeBetween('now', '+10 days'),
            'shipped_at'       => null,
            'delivered_at'     => null,
        ];
    }

    public function pending(): static
    {
        return $this->state([
            'status'       => ShipmentStatus::PENDING,
            'shipped_at'   => null,
            'delivered_at' => null,
        ]);
    }

    public function processing(): static
    {
        return $this->state([
            'status'       => ShipmentStatus::PROCESSING,
            'shipped_at'   => null,
            'delivered_at' => null,
        ]);
    }

    public function shipped(): static
    {
        return $this->state([
            'status'       => ShipmentStatus::SHIPPED,
            'shipped_at'   => now(),
            'delivered_at' => null,
        ]);
    }

    public function delivered(): static
    {
        return $this->state([
            'status'       => ShipmentStatus::DELIVERED,
            'shipped_at'   => fake()->dateTimeBetween('-10 days', '-2 days'),
            'delivered_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'status'       => ShipmentStatus::FAILED,
            'shipped_at'   => null,
            'delivered_at' => null,
        ]);
    }
}
