<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Wishlist>
 */
class WishlistFactory extends Factory
{
    protected $model = \App\Models\Wishlist::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'pizza_id' => \App\Models\Pizza::inRandomOrder()->first()?->id ?? 1,
        ];
    }
}