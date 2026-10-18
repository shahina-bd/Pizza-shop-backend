<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = \App\Models\Review::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::inRandomOrder()->first()?->id ?? 1,
            'pizza_id' => \App\Models\Pizza::inRandomOrder()->first()?->id ?? 1,
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(3),
            'comment' => fake()->paragraph(),
            'is_approved' => fake()->boolean(80),
        ];
    }
}