<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pizza;
use App\Models\Review;
use App\Models\Slider;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(RolePermissionSeeder::class);

        $admin = User::create([
            'name' => 'Admin User',
            'mobile' => '01700000000',
            'email' => 'admin@example.com',
            'address' => 'Dhaka, Bangladesh',
            'password' => bcrypt('admin@123')
        ]);
        $admin->assignRole('admin');

        $vendor = User::create([
            'name' => 'Vendor User',
            'mobile' => '01711111111',
            'email' => 'vendor@example.com',
            'address' => 'Dhaka, Bangladesh',
            'password' => bcrypt('vendor@123')
        ]);
        $vendor->assignRole('vendor');

        $moderator = User::create([
            'name' => 'Moderator User',
            'mobile' => '01722222222',
            'email' => 'moderator@example.com',
            'address' => 'Dhaka, Bangladesh',
            'password' => bcrypt('moderator@123')
        ]);
        $moderator->assignRole('moderator');

        $customer = User::create([
            'name' => 'Customer User',
            'mobile' => '01733333333',
            'email' => 'customer@example.com',
            'address' => 'Dhaka, Bangladesh',
            'password' => bcrypt('customer@123')
        ]);
        $customer->assignRole('customer');

        \App\Models\Category::insert([
            ['name' => 'Electronics', 'image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mobile Phones', 'image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Laptops', 'image' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
        Category::factory()->count(5)->create();

        $this->call(PizzaSeeder::class);
        Pizza::factory()->count(10)->create();

        Slider::create(['title' => 'Summer Sale', 'subtitle' => 'Up to 50% off', 'image' => 'slider1.jpg', 'order' => 1, 'is_active' => true]);
        Slider::create(['title' => 'New Arrivals', 'subtitle' => 'Latest pizzas', 'image' => 'slider2.jpg', 'order' => 2, 'is_active' => true]);

        User::factory()->count(10)->create()->each(function ($user) {
            $user->assignRole('customer');
        });

        $customers = User::role('customer')->get();
        $pizzas = Pizza::all();

        foreach ($customers as $customer) {
            Cart::create(['user_id' => $customer->id]);
        }

        $carts = Cart::all();
        foreach ($carts as $cart) {
            $itemCount = rand(1, 3);
            for ($j = 0; $j < $itemCount; $j++) {
                $pizza = $pizzas->random();
                CartItem::create([
                    'cart_id' => $cart->id,
                    'pizza_id' => $pizza->id,
                    'quantity' => rand(1, 5)
                ]);
            }
        }

        foreach ($customers as $customer) {
            $orderCount = rand(1, 3);
            for ($i = 0; $i < $orderCount; $i++) {
                $order = Order::create([
                    'user_id' => $customer->id,
                    'total' => rand(500, 5000),
                    'status' => collect(['pending', 'processing', 'completed', 'cancelled'])->random(),
                    'payment_status' => collect(['pending', 'paid', 'failed'])->random(),
                    'address' => fake()->address(),
                ]);

                $itemCount = rand(1, 4);
                for ($j = 0; $j < $itemCount; $j++) {
                    $pizza = $pizzas->random();
                    OrderItem::create([
                        'order_id' => $order->id,
                        'pizza_id' => $pizza->id,
                        'quantity' => rand(1, 3),
                        'price' => $pizza->price
                    ]);
                }

                Invoice::create([
                    'order_id' => $order->id,
                    'invoice_number' => 'INV-' . rand(1000, 9999),
                    'status' => 'generated',
                ]);
            }
        }

        // Create wishlists with unique user/pizza combinations
        $wishlistData = [];
        $users = User::role('customer')->get();
        $seenWishlist = [];
        
        while (count($wishlistData) < 25) {
            $user = $users->random();
            $pizza = Pizza::inRandomOrder()->first();
            $key = $user->id . '-' . $pizza->id;
            if (!isset($seenWishlist[$key])) {
                $seenWishlist[$key] = true;
                $wishlistData[] = [
                    'user_id' => $user->id,
                    'pizza_id' => $pizza->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        Wishlist::insert($wishlistData);

        // Create reviews with unique user/pizza combinations
        $reviewData = [];
        $seenReviews = [];
        
        while (count($reviewData) < 30) {
            $user = $users->random();
            $pizza = Pizza::inRandomOrder()->first();
            $key = $user->id . '-' . $pizza->id;
            if (!isset($seenReviews[$key])) {
                $seenReviews[$key] = true;
                $reviewData[] = [
                    'user_id' => $user->id,
                    'pizza_id' => $pizza->id,
                    'rating' => rand(1, 5),
                    'title' => fake()->sentence(3),
                    'comment' => fake()->paragraph(),
                    'is_approved' => fake()->boolean(80),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        Review::insert($reviewData);
    }
}
