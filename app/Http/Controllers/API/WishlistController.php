<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;

class WishlistController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $userId = auth()->id();

        // Check if the wishlist entry exists (with or without soft delete)
        $wishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlist) {
            if ($wishlist->is_deleted == 0) {
                // Soft delete
                $wishlist->is_deleted = 1;
                $wishlist->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Product removed from wishlist',
                ]);
            } else {
                // Re-add it (toggle back)
                $wishlist->is_deleted = 0;
                $wishlist->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Product re-added to wishlist',
                    'data' => $wishlist
                ]);
            }
        } else {
            // Fresh insert
            $wishlist = Wishlist::create([
                'user_id' => $userId,
                'product_id' => $request->product_id,
                'is_deleted' => 0,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Product added to wishlist',
                'data' => $wishlist
            ]);
        }
    }

    public function index()
    {
        $wishlists = Wishlist::with('product')
            ->where('user_id', auth()->id())
            ->where('is_deleted', 0)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Wishlist fetched successfully',
            'data' => $wishlists
        ]);
    }
    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $userId = auth()->id();

        $wishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->where('is_deleted', 0)
            ->first();

        if ($wishlist) {
            $wishlist->is_deleted = 1;
            $wishlist->save();

            return response()->json([
                'status' => true,
                'message' => 'Product removed from wishlist'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Product not found in wishlist'
        ]);
    }
}
