<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $allowedStatuses = ['pending', 'processing', 'completed', 'cancelled'];

        $query = Order::where('user_id', $userId)
            ->withSum('items as item_quantity', 'quantity');

        if ($request->filled('status') && in_array($request->status, $allowedStatuses, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $orderId = (int) ltrim(trim((string) $request->search), '#');
            $query->where('id', $orderId);
        }

        $orders = $query->latest()->paginate(10);

        $base = Order::where('user_id', $userId);
        $stats = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'processing' => (clone $base)->where('status', 'processing')->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
            'total_spent' => (float) (clone $base)->where('status', '!=', 'cancelled')->sum('total'),
        ];

        return response()->json(array_merge($orders->toArray(), ['stats' => $stats]));
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized order access.');
        }

        $order->load(['items.pizza', 'user']);

        return response()->json([
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized order access.');
        }

        $data = $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        $order->update($data);

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }
}
