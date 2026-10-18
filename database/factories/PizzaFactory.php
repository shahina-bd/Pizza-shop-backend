<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pizza>
 */
class PizzaFactory extends Factory
{
    protected $model = \App\Models\Pizza::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(200, 1000),
            'image' => 'pizza' . fake()->numberBetween(1, 8) . '.jpg',
            'size' => fake()->randomElement(['Small', 'Medium', 'Large']),
            'stock' => fake()->numberBetween(10, 50),
            'status' => true,
        ];
    }
}