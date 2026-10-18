<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    protected $model = \App\Models\Cart::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
        ];
    }
}