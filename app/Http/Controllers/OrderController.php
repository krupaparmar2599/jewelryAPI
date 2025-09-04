<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function placeOrder(Request $request)
    {
        $user = Auth::user();

        // Get user's cart items
        $cartItems = Cart::with('product')
            ->where('user_id', $user->id)
            ->where('is_deleted', 0)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Cart is empty']);
        }

        // Handle coupon
        $couponCode = $request->coupon_code;
        $discount = 0;

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon) {
                $discount = $coupon->discount;
            }
        }

        // Calculate totals
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item->product->price * $item->quantity;
        }

        $finalTotal = $total - $discount;

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total_amount' => $total,
            'coupon_code' => $couponCode,
            'discount' => $discount,
            'final_total' => $finalTotal,
        ]);

        // Create order items
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
            ]);
        }

        // Clear the cart
        Cart::where('user_id', $user->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Order placed successfully',
            'order_id' => $order->id,
        ]);
    }
    public function getOrders()
    {
        $userId = Auth::id();

        $currentOrders = Order::with('orderItems.product')
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing']) // or whatever your current status is
            ->get();

        $pastOrders = Order::with('orderItems.product')
            ->where('user_id', $userId)
            ->whereIn('status', ['completed', 'cancelled']) // or shipped/delivered etc.
            ->get();

        return response()->json([
            'status' => true,
            'current_orders' => $currentOrders,
            'past_orders' => $pastOrders,
        ]);
    }
}
