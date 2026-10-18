<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PizzaUpdateRequest;
use App\Http\Resources\PizzaResource;
use App\Models\Pizza;
use Illuminate\Http\Request;

class PizzaController extends Controller
{
    public function index(Request $request)
    {
        $query = Pizza::with(['category']);

        if (! $request->boolean('admin')) {
            $query->where('status', true);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $defaultPerPage = $request->boolean('admin') ? 50 : 6;
        $perPage = $request->integer('per_page', $defaultPerPage);
        $pizzas = $query->latest()->paginate($perPage);

        return PizzaResource::collection($pizzas);
    }

    public function latest()
    {
        $pizzas = Pizza::with(['category'])
            ->where('status', true)
            ->latest()
            ->limit(10)
            ->get();

        return PizzaResource::collection($pizzas);
    }

    public function popular()
    {
        $pizzas = Pizza::with(['category'])
            ->where('status', true)
            ->latest()
            ->limit(10)
            ->get();

        return PizzaResource::collection($pizzas);
    }

    public function featured()
    {
        $pizzas = Pizza::with(['category'])
            ->where('status', true)
            ->latest()
            ->limit(10)
            ->get();

        return PizzaResource::collection($pizzas);
    }

    public function show(Pizza $pizza)
    {
        abort_if(! $pizza->status, 404);

        $pizza->load(['category']);

        $related = Pizza::with(['category'])
            ->where('status', true)
            ->where('category_id', $pizza->category_id)
            ->where('id', '!=', $pizza->id)
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'pizza' => new PizzaResource($pizza),
            'related_pizzas' => PizzaResource::collection($related),
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->has('price') && $request->has('selling_price')) {
            $request->merge(['price' => $request->input('selling_price')]);
        }
        if (! $request->has('status') && $request->has('is_active')) {
            $request->merge(['status' => $request->input('is_active')]);
        }

        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|string',
            'size' => 'nullable|string',
            'extras' => 'nullable|array',
            'extras.*.name' => 'required_with:extras|string|max:100',
            'extras.*.price' => 'required_with:extras|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->boolean('status', true);

        $pizza = Pizza::create($data);

        return response()->json([
            'success' => true,
            'data' => new PizzaResource($pizza),
        ], 201);
    }

    public function update(PizzaUpdateRequest $request, Pizza $pizza)
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');

        $pizza->update($data);

        $pizza->load(['category']);

        return response()->json([
            'success' => true,
            'message' => 'Pizza updated successfully.',
            'data' => new PizzaResource($pizza),
        ]);
    }

    public function destroy(Pizza $pizza)
    {
        $pizza->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pizza deleted successfully.',
        ]);
    }
}
