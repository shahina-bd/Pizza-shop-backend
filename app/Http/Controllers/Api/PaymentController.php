<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pizza;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Raziul\Sslcommerz\Facades\Sslcommerz;

class PaymentController extends Controller
{
    // চেকআউট - অর্ডার তৈরি ও পেমেন্ট ইনির্শিয়েট
    public function checkout(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'payment_method' => 'required|string|in:cod,sslcommerz',
            'items' => 'required|array',
            'items.*.pizza_id' => 'required|integer|exists:pizzas,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.extras' => 'nullable|array',
            'items.*.extras.*.name' => 'required_with:items.*.extras|string|max:100',
            'items.*.extras.*.price' => 'required_with:items.*.extras|numeric|min:0',
        ]);
        
        $user = $request->user();
        $cartItems = $request->input('items');
        
        // ক্যালকুলেশন
        $subtotal = 0;
        $orderItems = [];
        foreach ($cartItems as $item) {
            $pizza = Pizza::find($item['pizza_id']);
            $quantity = (int) $item['quantity'];

            $extras = [];
            $extrasUnitTotal = 0.0;
            if (!empty($item['extras']) && is_array($item['extras'])) {
                foreach ($item['extras'] as $extra) {
                    $extraPrice = isset($extra['price']) ? (float) $extra['price'] : 0.0;
                    $extrasUnitTotal += $extraPrice;
                    $extras[] = [
                        'name' => (string) ($extra['name'] ?? ''),
                        'price' => round($extraPrice, 2),
                    ];
                }
            }

            $unitPrice = (float) $pizza->price + $extrasUnitTotal;
            $subtotal += $quantity * $unitPrice;

            $orderItems[] = [
                'pizza_id' => $pizza->id,
                'price' => round($unitPrice, 2),
                'quantity' => $quantity,
                'extras' => !empty($extras) ? $extras : null,
            ];
        }
        
        $total = round($subtotal, 2);
        
        // অর্ডার ক্রিয়েট
        $order = Order::create([
            'user_id' => $user->id,
            'total' => $total,
            'payment_status' => 'pending',
            'status' => 'pending',
            'address' => $request->address,
        ]);
        
        // অর্ডার আইটেম সেভ
        foreach ($orderItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'pizza_id' => $item['pizza_id'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'extras' => $item['extras'],
            ]);
        }
        
        if ($request->input('payment_method') === 'sslcommerz') {
            $postDetails = Sslcommerz::setOrder((float)$total, (string)$order->id, 'Pizza Order')
                ->setCustomer(
                    $user->name ?? 'Guest',
                    $user->email ?? 'guest@example.com',
                    $user->mobile ?? '01XXXXXXXXX',
                    $request->address
                )
                ->setCallbackUrls(
                    route('sslc.success'),
                    route('sslc.failure'),
                    route('sslc.cancel'),
                    route('sslc.ipn')
                )
                ->makePayment();

            if ($postDetails->success()) {
                return response()->json([
                    'message' => 'Order created and payment initiated.',
                    'payment_url' => $postDetails->gatewayPageURL(),
                    'order_id' => $order->id,
                ]);
            } else {
                return response()->json([
                    'message' => 'Payment initiation failed: ' . $postDetails->failedReason()
                ], 400);
            }
        }
        
        return response()->json([
            'message' => 'Order successful.',
            'order_id' => $order->id,
        ]);
    }
    
    // পেমেন্ট সাকসেস কলব্যাক
    public function paymentSuccess(Request $request)
    {
        $transactionId = $request->input('tran_id');
        
        // অর্ডার খুঁজে বের করুন
        $order = is_numeric($transactionId) ? Order::find($transactionId) : null;
        
        if (!$order) {
            return redirect()->away(env('FRONTEND_URL') . '/payment/failed');
        }
        
        // ✅ সঠিকভাবে amount সহ validatePayment() কল করুন
        $isValid = Sslcommerz::validatePayment($request->all(), $transactionId, (float)$order->total);
        
        if ($isValid) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
            ]);
            
            // কার্ট ক্লিয়ার
            Cart::where('user_id', $order->user_id)->delete();

            $invoiceData = [
                'order_id' => $order->id,
                'pdf_path' => $this->generateInvoicePdf($order),
            ];

            Invoice::create($invoiceData);
            
            return redirect()->away(env('FRONTEND_URL') . '/payment/success?order=' . $order->id);
        }
        
        return redirect()->away(env('FRONTEND_URL') . '/payment/failed');
    }
    
    // পেমেন্ট ফেইল কলব্যাক
    public function paymentFailure(Request $request)
    {
        $orderId = $request->input('tran_id');
        $order = is_numeric($orderId) ? Order::find($orderId) : null;
        
        if ($order) {
            $order->update([
                'payment_status' => 'failed',
                'status' => 'cancelled'
            ]);
        }
        
        return redirect()->away(env('FRONTEND_URL') . '/payment/failed');
    }
    
    // পেমেন্ট ক্যান্সেল কলব্যাক
    public function paymentCancel(Request $request)
    {
        return redirect()->away(env('FRONTEND_URL') . '/payment/cancel');
    }
    
    // ✅ IPN Handler (সঠিকভাবে amount সহ)
    public function paymentIpn(Request $request)
    {
        \Log::info('SSLCommerz IPN Called', $request->all());
        
        $transactionId = $request->input('tran_id');
        
        if (!$transactionId) {
            return response()->json(['status' => 'error', 'message' => 'No transaction ID'], 400);
        }
        
        $order = is_numeric($transactionId) ? Order::find($transactionId) : null;
        
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }
        
        // ✅ সঠিকভাবে amount সহ validatePayment() কল করুন
        $isValid = Sslcommerz::validatePayment($request->all(), $transactionId, (float)$order->total);
        
        if ($isValid) {
            if ($order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                ]);
                
                // কার্ট ক্লিয়ার
                Cart::where('user_id', $order->user_id)->delete();
                
                \Log::info('Order paid via IPN', ['order_id' => $order->id]);
            }
            
            return response()->json(['status' => 'success']);
        }
        
        \Log::error('IPN Validation Failed', ['tran_id' => $transactionId]);
        
        return response()->json(['status' => 'failed'], 400);
    }

    public function generateInvoicePdf($order) 
    {
        // Pdf::
    //     $pdf = Pdf::loadView('pdf.invoice', $data);
    // return $pdf->download('invoice.pdf');
        return 'pdf path';    
    }
}