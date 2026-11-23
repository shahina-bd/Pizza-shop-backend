<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{
    private function getStoragePath(): string
    {
        return storage_path('app/settings.json');
    }

    private function defaultData(): array
    {
        return [
            // Store Profile
            'restaurant_name' => 'Pizza Delicious',
            'tagline' => 'Artisan Pizzas & Authentic Italian Cuisine',
            'contact_phone' => '+880 1700-000000',
            'contact_email' => 'info@pizzaabd.shop',
            'store_address' => 'House 12, Road 5, Dhanmondi, Dhaka 1205',
            'opening_hours' => '10:00 AM - 11:00 PM (Everyday)',

            // Order & Delivery
            'is_store_open' => true,
            'delivery_fee' => 50,
            'free_delivery_min_amount' => 1000,
            'estimated_delivery_time' => '30 - 45 Minutes',
            'currency_symbol' => '৳',
            'min_order_amount' => 200,

            // Notifications & Sound
            'sound_notification_enabled' => true,
            'desktop_notification_enabled' => true,

            // Social Media & Footer
            'facebook_url' => 'https://facebook.com',
            'instagram_url' => 'https://instagram.com',
            'whatsapp_number' => '+8801700000000',
            'copyright_text' => '© ' . date('Y') . ' Pizza Delicious. All Rights Reserved.',
        ];
    }

    private function readData(): array
    {
        $path = $this->getStoragePath();
        $defaults = $this->defaultData();

        if (!File::exists($path)) {
            return $defaults;
        }

        $decoded = json_decode(File::get($path), true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        return array_merge($defaults, $decoded);
    }

    public function show()
    {
        return response()->json([
            'success' => true,
            'data' => $this->readData(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'restaurant_name' => 'nullable|string|max:120',
            'tagline' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:40',
            'contact_email' => 'nullable|email|max:120',
            'store_address' => 'nullable|string|max:300',
            'opening_hours' => 'nullable|string|max:120',

            'is_store_open' => 'nullable|boolean',
            'delivery_fee' => 'nullable|numeric|min:0',
            'free_delivery_min_amount' => 'nullable|numeric|min:0',
            'estimated_delivery_time' => 'nullable|string|max:80',
            'currency_symbol' => 'nullable|string|max:10',
            'min_order_amount' => 'nullable|numeric|min:0',

            'sound_notification_enabled' => 'nullable|boolean',
            'desktop_notification_enabled' => 'nullable|boolean',

            'facebook_url' => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:40',
            'copyright_text' => 'nullable|string|max:255',
        ]);

        $current = $this->readData();
        $updated = array_merge($current, $validated);

        if ($request->has('is_store_open')) {
            $updated['is_store_open'] = $request->boolean('is_store_open');
        }
        if ($request->has('sound_notification_enabled')) {
            $updated['sound_notification_enabled'] = $request->boolean('sound_notification_enabled');
        }
        if ($request->has('desktop_notification_enabled')) {
            $updated['desktop_notification_enabled'] = $request->boolean('desktop_notification_enabled');
        }

        $dir = dirname($this->getStoragePath());
        if (!File::exists($dir)) {
            @File::makeDirectory($dir, 0755, true);
        }

        File::put($this->getStoragePath(), json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
            'data' => $updated,
        ]);
    }
}
