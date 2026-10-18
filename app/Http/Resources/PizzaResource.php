<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PizzaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imagePath = $this->image;

        $imageFullUrl = $imagePath
            ? asset(Storage::url($imagePath))
            : asset('images/no-image.png');

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => number_format((float) $this->price, 2, '.', ''),
            'image' => $this->image,
            'image_url' => $imageFullUrl,
            'size' => $this->size,
            'extras' => is_array($this->extras)
                ? array_values(array_map(fn ($item) => [
                    'name' => (string) ($item['name'] ?? ''),
                    'price' => round((float) ($item['price'] ?? 0), 2),
                ], array_filter($this->extras, fn ($item) => ! empty($item['name']))))
                : \App\Models\Pizza::defaultExtras(),
            'stock' => (int) ($this->stock ?? 0),
            'status' => (bool) $this->status,
            'is_active' => (bool) $this->status,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category?->id,
                    'name' => $this->category?->name,
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
