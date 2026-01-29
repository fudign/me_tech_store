<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'customer_name' => fake()->name(),
            'customer_phone' => '+996' . fake()->numerify('7########'),
            'customer_address' => fake()->address(),
            'payment_method' => fake()->randomElement(['cash', 'online', 'installment']),
            'status' => Order::STATUS_NEW,
            'subtotal' => fake()->numberBetween(10000, 100000), // In cents
            'total' => fake()->numberBetween(10000, 100000), // In cents
        ];
    }

    /**
     * Indicate that the order is being processed.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_PROCESSING,
        ]);
    }

    /**
     * Indicate that the order is being delivered.
     */
    public function delivering(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_DELIVERING,
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_COMPLETED,
        ]);
    }
}
