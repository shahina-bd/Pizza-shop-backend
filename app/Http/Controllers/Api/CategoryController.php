<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('pizzas');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->latest('id')->get();
        return response()->json($categories);
    }

    public function show($id)
    {
        $category = Category::withCount('pizzas')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable',
            'image_file' => 'nullable|image|max:5120',
        ]);

        $imageUrl = $request->input('image');

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/categories');
            if (!file_exists($destination)) {
                @mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $imageUrl = url('uploads/categories/' . $filename);
        } elseif ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/categories');
            if (!file_exists($destination)) {
                @mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $imageUrl = url('uploads/categories/' . $filename);
        }

        $category = Category::create([
            'name' => $request->name,
            'image' => $imageUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable',
            'image_file' => 'nullable|image|max:5120',
        ]);

        $imageUrl = $request->input('image', $category->image);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/categories');
            if (!file_exists($destination)) {
                @mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $imageUrl = url('uploads/categories/' . $filename);
        } elseif ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/categories');
            if (!file_exists($destination)) {
                @mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $imageUrl = url('uploads/categories/' . $filename);
        }

        $category->update([
            'name' => $request->name,
            'image' => $imageUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }

    public function pizzas(Category $category)
    {
        $pizzas = $category->pizzas()->paginate(20);
        return response()->json($pizzas);
    }
}
