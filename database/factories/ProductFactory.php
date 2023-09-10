<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $precio = fake()->randomFloat(2, 1, 100);

        return [
            'internal_code' => fake()->unique()->ean13(),

            'slug' => fake()->word(),
            'name' => fake()->word(),
            'description' => fake()->text(100),

            'sale_price' => $precio * 1.1,
            'purchase_price' => $precio,

            'stock' => fake()->numberBetween(0, 15),

            'is_weighable' => fake()->boolean(30),
            'is_active' => fake()->boolean(95),
        ];
    }
}
