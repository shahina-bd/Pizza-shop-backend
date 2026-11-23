<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class HeroBannerController extends Controller
{
    private function getStoragePath(): string
    {
        return storage_path('app/hero_banner.json');
    }

    private function defaultData(): array
    {
        return [
            'subtitle' => 'Delicious',
            'title' => 'Italian Cuisine',
            'description' => 'A small river named Duden flows by their place and supplies it with the necessary regelialia. It is a paradise.',
            'primary_button_text' => 'Order Now',
            'primary_button_link' => '/cart',
            'secondary_button_text' => 'View Menu',
            'secondary_button_link' => '/menu',
            'media_type' => 'video',
            'video_url' => 'https://assets.mixkit.co/videos/42463/42463-720.mp4',
            'image_url' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=600&auto=format&fit=crop',
            'banner_bg_url' => '',
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
        $request->validate([
            'subtitle' => 'nullable|string|max:120',
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:1000',
            'primary_button_text' => 'nullable|string|max:80',
            'primary_button_link' => 'nullable|string|max:255',
            'secondary_button_text' => 'nullable|string|max:80',
            'secondary_button_link' => 'nullable|string|max:255',
            'media_type' => 'nullable|in:video,image',
            'video_url' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:1000',
            'banner_bg_url' => 'nullable|string|max:1000',
            'video_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg,video/quicktime|max:51200',
            'image_file' => 'nullable|image|max:10240',
            'banner_bg_file' => 'nullable|image|max:10240',
        ]);

        $current = $this->readData();

        $textFields = [
            'subtitle',
            'title',
            'description',
            'primary_button_text',
            'primary_button_link',
            'secondary_button_text',
            'secondary_button_link',
            'media_type',
            'video_url',
            'image_url',
            'banner_bg_url',
        ];

        foreach ($textFields as $field) {
            if ($request->exists($field)) {
                $current[$field] = (string) ($request->input($field) ?? '');
            }
        }

        $uploadDir = public_path('uploads/banner');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0775, true);
        }

        if ($request->hasFile('video_file')) {
            $file = $request->file('video_file');
            $filename = 'hero_video_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $current['video_url'] = url('uploads/banner/' . $filename);
            $current['media_type'] = 'video';
        }

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = 'hero_image_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $current['image_url'] = url('uploads/banner/' . $filename);
        }

        if ($request->hasFile('banner_bg_file')) {
            $file = $request->file('banner_bg_file');
            $filename = 'hero_bg_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $current['banner_bg_url'] = url('uploads/banner/' . $filename);
        }

        if ($request->boolean('clear_banner_bg')) {
            $current['banner_bg_url'] = '';
        }

        File::put($this->getStoragePath(), json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json([
            'success' => true,
            'message' => 'Hero banner updated successfully.',
            'data' => $current,
        ]);
    }
}
