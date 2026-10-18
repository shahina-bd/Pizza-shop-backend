<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Pizza;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function sales(Request $request)
    {
        $query = Order::query();

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $salesData = $query->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(id) as orders'),
            DB::raw('SUM(total) as revenue')
        )
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->get();

        $totalSales = $salesData->sum('revenue');
        $totalOrders = $salesData->sum('orders');
        
        $totalCustomers = Order::when($request->filled('start_date'), function($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->start_date);
        })
        ->when($request->filled('end_date'), function($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->end_date);
        })
        ->distinct('user_id')
        ->count('user_id');

        return response()->json([
            'total_sales' => (float)$totalSales,
            'total_orders' => (int)$totalOrders,
            'total_customers' => $totalCustomers,
            'data' => $salesData
        ]);
    }

    public function customers(Request $request)
    {
        $query = User::select('users.id', 'users.name', 'users.email', 'users.mobile')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->selectRaw('COUNT(orders.id) as order_count')
            ->selectRaw('SUM(orders.total) as total_spent');

        if ($request->filled('start_date')) {
            $query->whereDate('orders.created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('orders.created_at', '<=', $request->end_date);
        }

        $customerData = $query->groupBy('users.id', 'users.name', 'users.email', 'users.mobile')
            ->orderBy('total_spent', 'desc')
            ->get();

        $totalCustomers = User::whereHas('roles', function($q) {
            $q->where('name', 'customer');
        })->count();

        return response()->json([
            'total_customers' => $totalCustomers,
            'data' => $customerData
        ]);
    }

    public function orders(Request $request)
    {
        $query = Order::with('user');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->latest()->get();

        $mappedOrders = $orders->map(function($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->id,
                'customer_name' => $order->user->name ?? 'Guest',
                'total' => (float)$order->total,
                'order_status' => $order->status,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at,
            ];
        });

        return response()->json([
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'data' => $mappedOrders
        ]);
    }

    public function pizzas(Request $request)
    {
        $query = Pizza::select('pizzas.id', 'pizzas.name', 'pizzas.stock', 'categories.name as category')
            ->leftJoin('categories', 'pizzas.category_id', '=', 'categories.id')
            ->leftJoin('order_items', 'pizzas.id', '=', 'order_items.pizza_id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as sales_count')
            ->selectRaw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as revenue');

        if ($request->filled('start_date')) {
            $query->whereDate('orders.created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('orders.created_at', '<=', $request->end_date);
        }

        $productData = $query->groupBy('pizzas.id', 'pizzas.name', 'pizzas.stock', 'categories.name')
            ->orderBy('sales_count', 'desc')
            ->get();

        return response()->json([
            'data' => $productData
        ]);
    }
}
