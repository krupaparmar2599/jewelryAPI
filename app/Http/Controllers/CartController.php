<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\UserCoupon;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = auth()->id();

        $cart = Cart::updateOrCreate(
            [
                'user_id' => $userId,
                'product_id' => $request->product_id,
                // ],
                // [
                'quantity' => $request->quantity,
                'is_deleted' => 0,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Product added to cart',
            'data' => $cart
        ]);
    }
    public function changeQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'action' => 'required|in:increase,decrease',
        ]);

        $userId = auth()->id();

        $cart = Cart::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$cart) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found in cart',
            ]);
        }

        if ($request->action === 'increase') {
            $cart->quantity += 1;
            $cart->save();
        } elseif ($request->action === 'decrease') {
            if ($cart->quantity > 1) {
                $cart->quantity -= 1;
                $cart->save();
            } else {
                $cart->delete();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Cart updated successfully',
            'data' => $cart
        ]);
    }
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $userId = auth()->id();

        $cart = Cart::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$cart) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found in cart',
            ]);
        }

        $cart->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product removed from cart',
        ]);
    }
    public function clearCart()
    {
        $userId = auth()->id();

        $deleted = Cart::where('user_id', $userId)->delete();

        return response()->json([
            'status' => true,
            'message' => $deleted ? 'Cart cleared successfully' : 'Cart was already empty',
        ]);
    }

    // public function viewCart()
    // {
    //     $userId = auth()->id();

    //     $cartItems = Cart::with('product') // assuming relation is defined
    //         ->where('user_id', $userId)
    //         ->get();

    //     $totalQuantity = $cartItems->sum('quantity');
    //     $totalPrice = $cartItems->sum(function ($item) {
    //         return $item->quantity * $item->product->price;
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Cart items fetched successfully',
    //         'data' => [
    //             'items' => $cartItems,
    //             'total_quantity' => $totalQuantity,
    //             'total_price' => $totalPrice
    //         ]
    //     ]);
    // }
    public function viewCart()
    {
        $userId = auth()->id();

        $cartItems = Cart::with('product')->where('user_id', $userId)->get();

        $total = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);

        // Fetch applied coupon if any
        $userCoupon = UserCoupon::with('coupon')
            ->where('user_id', $userId)
            ->where('is_applied', true)
            ->latest()->first();

        $discount = 0;
        $couponCode = null;

        if ($userCoupon && $userCoupon->coupon) {
            $coupon = $userCoupon->coupon;
            $couponCode = $coupon->code;

            if ($coupon->discount_amount) {
                $discount = $coupon->discount_amount;
            } elseif ($coupon->discount_percent) {
                $discount = ($total * $coupon->discount_percent) / 100;
            }
        }

        return response()->json([
            'status' => true,
            'cart_items' => $cartItems,
            'coupon_applied' => $couponCode,
            'discount' => $discount,
            'final_total' => max(0, $total - $discount)
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $userId = auth()->id();
        $cartItems = Cart::with('product')->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Cart is empty']);
        }

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json(['status' => false, 'message' => 'Invalid coupon code']);
        }

        if ($coupon->expires_at && $coupon->expires_at < now()) {
            return response()->json(['status' => false, 'message' => 'Coupon expired']);
        }

        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            return response()->json(['status' => false, 'message' => 'Coupon usage limit reached']);
        }

        // Check if user already applied this coupon
        $alreadyUsed = UserCoupon::where('user_id', $userId)
            ->where('coupon_id', $coupon->id)
            ->where('is_applied', true)
            ->first();

        if ($alreadyUsed) {
            return response()->json(['status' => false, 'message' => 'Coupon already used']);
        }

        // Calculate cart total
        $total = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);

        // Discount calculation
        $discount = 0;
        if ($coupon->discount_amount) {
            $discount = $coupon->discount_amount;
        } elseif ($coupon->discount_percent) {
            $discount = ($total * $coupon->discount_percent) / 100;
        }

        $totalAfterDiscount = max(0, $total - $discount);

        // Save coupon usage
        UserCoupon::create([
            'user_id' => $userId,
            'coupon_id' => $coupon->id,
            'is_applied' => true
        ]);

        // Increment usage count
        // $coupon->increment('used_count');

        return response()->json([
            'status' => true,
            'message' => 'Coupon applied successfully',
            'data' => [
                'original_total' => $total,
                'discount' => $discount,
                'final_total' => $totalAfterDiscount,
                'coupon_code' => $coupon->code
            ]   
        ]);
    }
}
