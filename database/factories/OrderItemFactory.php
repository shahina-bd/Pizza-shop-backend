<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Pizza;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = \App\Models\OrderItem::class;

    public function definition(): array
    {
        $pizza = Pizza::inRandomOrder()->first() ?? Pizza::factory();
        return [
            'order_id' => Order::factory(),
            'pizza_id' => $pizza->id,
            'quantity' => fake()->numberBetween(1, 3),
            'price' => $pizza->price,
        ];
    }
}