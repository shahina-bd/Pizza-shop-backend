<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Pizza;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PizzaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create pizza categories
        $categories = [
            ['name' => 'Vegetarian', 'image' => null],
            ['name' => 'Meat Lovers', 'image' => null],
            ['name' => 'Cheese', 'image' => null],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(['name' => $categoryData['name']], $categoryData);
        }

        // Get all pizza categories
        $pizzaCategories = Category::whereIn('name', [
            'Vegetarian', 'Meat Lovers', 'Cheese'
        ])->get();

        // Define pizza data
        $pizzaNames = [
            'Margherita', 'Pepperoni', 'Veggie Deluxe', 'Hawaiian',
            'Meat Supreme', 'Cheese Burst', 'Chicken Fiesta', 'Mushroom Lover',
            'Onion Ring', 'Olive Special', 'Tomato Basil', 'Garlic Bread',
            'Spicy Sausage', 'BBQ Chicken', 'Paneer Tikka', 'Corn & Cheese',
            'Seared Steak', 'Roasted Veg', 'Italian Sausage', 'Spinach & Feta'
        ];

        $sizes = ['Small', 'Medium', 'Large'];
        $images = [
            'pizza1.jpg', 'pizza2.jpg', 'pizza3.jpg', 'pizza4.jpg',
            'pizza5.jpg', 'pizza6.jpg', 'pizza7.jpg', 'pizza8.jpg'
        ];

        foreach ($pizzaCategories as $category) {
            // Create 5 pizzas per category
            for ($i = 1; $i <= 5; $i++) {
                $index = array_rand($pizzaNames);
                $pizzaName = $pizzaNames[$index];
                // Remove used name to avoid duplicates? Or allow duplicates? We'll allow for simplicity.
                // But let's remove to have unique names per category? Not necessary, but let's do without removal for now.

                Pizza::create([
                    'category_id' => $category->id,
                    'name' => $pizzaName . ' ' . ($i + 1), // To avoid exact duplicates
                    'description' => "Delicious " . strtolower($pizzaName) . " pizza made with fresh ingredients.",
                    'price' => rand(200, 1000),
                    'image' => $images[array_rand($images)],
                    'size' => $sizes[array_rand($sizes)],
                    'stock' => rand(10, 50),
                    'status' => true
                ]);
            }
        }
    }
}