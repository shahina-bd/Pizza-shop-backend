<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string',
        ]);

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'data' => $category,
        ], 201);
    }
    
    public function pizzas(Category $category)
    {
        $pizzas = $category->pizzas()->paginate(20);
        return response()->json($pizzas);
    }

}
