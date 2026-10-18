<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Pizza;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = \App\Models\CartItem::class;

    public function definition(): array
    {
        $pizza = Pizza::inRandomOrder()->first();
        return [
            'cart_id' => Cart::inRandomOrder()->first()?->id ?? 1,
            'pizza_id' => $pizza?->id ?? 1,
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }
}