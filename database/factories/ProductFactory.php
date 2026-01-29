<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'specifications' => [
                'Процессор' => fake()->randomElement(['Intel Core i5', 'Intel Core i7', 'AMD Ryzen 5']),
                'Память' => fake()->randomElement(['8GB', '16GB', '32GB']),
            ],
            'price' => fake()->numberBetween(50000, 500000), // In cents
            'old_price' => null,
            'stock' => fake()->numberBetween(0, 100),
            'availability_status' => fake()->randomElement(['in_stock', 'out_of_stock', 'pre_order']),
            'sku' => fake()->unique()->regexify('[A-Z]{3}[0-9]{5}'),
            'main_image' => null,
            'images' => [],
            'is_active' => true,
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
