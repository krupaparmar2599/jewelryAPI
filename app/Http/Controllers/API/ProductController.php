<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)->latest()->get();

        return response()->json([
            'products' => $products
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'material' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'type' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $product = Product::create($request->all());

        return response()->json([
            'message' => 'Product added successfully',
            'product' => $product
        ], 201);
    }
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product || !$product->is_active) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'product' => $product
        ]);
    }
    public function productsByCategory($categoryId)
    {
        try {
            $products = Product::where('category_id', $categoryId)->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found for this category.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Products fetched by category successfully.',
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function searchFilterGet(Request $request)
    {
        $query = Product::query();

        // Search by product name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Eager load category for relational output
        $products = $query->with('category')->get();

        return response()->json([
            'status' => true,
            'message' => 'Filtered products fetched successfully',
            'data' => $products
        ]);
    }
    public function searchFilter(Request $request)
    {
        return response()->json(['debug' => 'Reached searchFilterGet']);

        $query = Product::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->with('category')->get();

        return response()->json([
            'status' => true,
            'message' => 'Filtered products fetched successfully',
            'data' => $products
        ]);
    }
}
