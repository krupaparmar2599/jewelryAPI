<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\WishlistController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::put('/profile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/searchGet', [ProductController::class, 'searchFilterGet']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/category/{id}', [ProductController::class, 'productsByCategory']);
    Route::post('/products/search', [ProductController::class, 'searchFilter']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    Route::get('/categories-with-products', [CategoryController::class, 'withProducts']);

    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/remove', [WishlistController::class, 'remove']);

    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::post('/cart/change-quantity', [CartController::class, 'changeQuantity']);
    Route::post('/cart/remove', [CartController::class, 'removeFromCart']);
    Route::post('/cart/clearAll', [CartController::class, 'clearCart']);

    Route::get('/cart/view', [CartController::class, 'viewCart']);
    Route::post('cart/apply-coupon', [CartController::class, 'applyCoupon']);

    Route::get('/orders', [OrderController::class, 'getOrders']);
// });

Route::middleware('auth:sanctum')->post('/order/place', [OrderController::class, 'placeOrder']);
