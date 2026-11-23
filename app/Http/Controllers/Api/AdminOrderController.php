<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.pizza'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15);

        return response()->json($orders);
    }

    public function latestNotification(Request $request)
    {
        $latestOrder = Order::with('user:id,name,mobile')
            ->latest('id')
            ->first();

        if (!$latestOrder) {
            return response()->json(['has_order' => false]);
        }

        return response()->json([
            'has_order' => true,
            'latest_order_id' => $latestOrder->id,
            'total' => $latestOrder->total,
            'status' => $latestOrder->status,
            'customer_name' => $latestOrder->user->name ?? 'Customer',
            'created_at' => $latestOrder->created_at ? $latestOrder->created_at->toIso8601String() : null,
        ]);
    }

    public function show($id)
    {
        $order = Order::with(['user', 'items.pizza'])->findOrFail($id);

        return response()->json($order);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $data = $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled,shipped',
        ]);

        $order->update([
            'status' => $data['status']
        ]);

        // Send FCM notification to customer
        try {
            $fcm = app(\App\Services\FirebaseNotificationService::class);
            $order->loadMissing('user');
            if ($order->user) {
                if ($data['status'] === 'shipped') {
                    $fcm->sendToUser($order->user, "🛵 Pizza On The Way!", "Order #{$order->id} is out for delivery!");
                } elseif ($data['status'] === 'processing') {
                    $fcm->sendToUser($order->user, "🍕 Order Being Prepared", "Order #{$order->id} is now in the oven!");
                } elseif ($data['status'] === 'completed') {
                    $fcm->sendToUser($order->user, "🎉 Order Delivered!", "Order #{$order->id} has been delivered. Enjoy!");
                } elseif ($data['status'] === 'cancelled') {
                    $fcm->sendToUser($order->user, "❌ Order Cancelled", "Order #{$order->id} has been cancelled.");
                } else {
                    $fcm->sendToUser($order->user, "🛵 Pizza On The Way!", "Order #{$order->id} is out for delivery!");
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[FCM] Customer notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'order' => $order
        ]);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $data = $request->validate([
            'payment_status' => 'required|string|in:pending,paid,failed',
        ]);

        $order->update([
            'payment_status' => $data['payment_status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'order' => $order
        ]);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
    }
}
