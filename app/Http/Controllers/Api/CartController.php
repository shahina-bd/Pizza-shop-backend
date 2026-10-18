<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // View cart
    public function index(Request $request)
    {
        $cartItems = CartItem::with('pizza')
        ->whereHas('cart', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })
        ->get();
        
        $total = $cartItems->sum(function($item) {
            return $item->quantity * $item->pizza->price;
        });
        
        return response()->json([
            'items' => $cartItems,
            'total_items' => $cartItems->sum('quantity'),
            'total_price' => $total
        ]);
    }
    
    // Add to cart
    public function add(Request $request)
    {
        $request->validate([
            'pizza_id' => 'required|exists:pizzas,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $cart = Cart::firstOrCreate(
            [
                'user_id' => $request->user()->id
            ]
        );
        
        $cartItem = CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'pizza_id' => $request->pizza_id
            ],
            [
                'quantity' => DB::raw('quantity + ' . $request->quantity)
            ]
        );
        
        return response()->json(['message' => 'Added to cart', 'cart' => $cart]);
    }
    
    // Update quantity
    public function update(Request $request, CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate(['quantity' => 'required|integer|min:1']);
        $cartItem->update(['quantity' => $request->quantity]);
        
        return response()->json(['message' => 'Cart updated', 'item' => $cartItem]);
    }
    
    // Remove item
    public function remove(Request $request, CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $cartItem->delete();
        return response()->json(['message' => 'Item removed from cart']);
    }
    
    // Clear cart
    public function clear(Request $request)
    {
        Cart::where('user_id', $request->user()->id)->delete();
        return response()->json(['message' => 'Cart cleared']);
    }
}