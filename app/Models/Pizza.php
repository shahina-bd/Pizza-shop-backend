<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pizza extends Model
{
    use HasFactory;
    
    protected $table = 'pizzas';
    protected $fillable = ['category_id', 'name', 'description', 'price', 'image', 'size', 'extras', 'stock', 'status'];

    protected $casts = [
        'extras' => 'array',
        'status' => 'boolean',
    ];

    public static function defaultExtras(): array
    {
        return [
            ['name' => 'Bufala', 'price' => 4.00],
            ['name' => 'Burrata', 'price' => 4.00],
            ['name' => 'Lachs', 'price' => 4.00],
            ['name' => 'Parmaschinken', 'price' => 4.00],
            ['name' => 'Garnelen', 'price' => 4.00],
            ['name' => 'Funghi', 'price' => 2.50],
            ['name' => 'gekochterschinken', 'price' => 2.50],
            ['name' => 'Salame', 'price' => 2.50],
            ['name' => 'Spinat', 'price' => 2.50],
            ['name' => 'Gorgonzola', 'price' => 2.50],
        ];
    }
    
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
    
    // Relations
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }


    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }
    
    public function allReviews()
    {
        return $this->hasMany(Review::class);
    }
    
    // এভারেজ রেটিং ক্যালকুলেশন
    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating'), 1);
    }
    
    // রেটিং ডিস্ট্রিবিউশন (১-৫ তারকার সংখ্যা)
    public function getRatingDistributionAttribute()
    {
        // $distribution = [];
        // for ($i = 1; $i <= 5; $i++) {
        //     $distribution[$i] = $this->reviews()->where('rating', $i)->count();
        // }
        // return $distribution;

        return $this->reviews()
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->all();


    }
    
    // রিভিউ কাউন্ট
    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }


}
