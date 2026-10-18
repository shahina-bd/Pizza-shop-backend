<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Pizza;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // পিজার সব রিভিউ দেখা
    public function index($pizzaId)
    {
        $pizza = Pizza::findOrFail($pizzaId);
        
        $reviews = Review::with('user')
            ->where('pizza_id', $pizzaId)
            ->approved()
            ->latest()
            ->paginate(10);
        
        return response()->json([
            'average_rating' => $pizza->average_rating,
            'total_reviews' => $pizza->reviews_count,
            'rating_distribution' => $pizza->rating_distribution,
            'reviews' => $reviews
        ]);
    }
    
    // রিভিউ দেওয়া
    public function store(Request $request)
    {
        $request->validate([
            'pizza_id' => 'required|exists:pizzas,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|min:5'
        ]);
        
        // আগে রিভিউ দিয়ে থাকলে আপডেট করবে
        $review = Review::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'pizza_id' => $request->pizza_id
            ],
            [
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'is_approved' => false // নতুন রিভিউ মডারেশনের জন্য পেন্ডিং
            ]
        );
        
        return response()->json([
            'message' => 'Review submitted successfully. Waiting for approval.',
            'review' => $review
        ], 201);
    }
    
    // আমার দেওয়া রিভিউ দেখা
    public function myReviews(Request $request)
    {
        $reviews = Review::with('pizza')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);
        
        return response()->json($reviews);
    }
    
    // রিভিউ ডিলিট
    public function remove(Request $request, $id)
    {
        $review = Review::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();
        
        $review->delete();
        
        return response()->json(['message' => 'Review deleted successfully']);
    }
}

