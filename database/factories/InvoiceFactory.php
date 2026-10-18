<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = \App\Models\Invoice::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'invoice_number' => 'INV-' . fake()->unique()->numberBetween(1000, 9999),
            'pdf_path' => null,
            'status' => fake()->randomElement(['generated', 'sent']),
        ];
    }
}