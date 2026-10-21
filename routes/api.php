<?php

use App\Infrastructure\Http\Controllers\Auth\AuthController;
use App\Infrastructure\Http\Controllers\Catalog\CategoryController;
use App\Infrastructure\Http\Controllers\Catalog\ProductController;
use App\Infrastructure\Http\Controllers\Sales\OrderController;
use App\Infrastructure\Http\Controllers\Store\AddressController;
use App\Infrastructure\Http\Controllers\Store\StoreController;
use App\Infrastructure\Http\Resources\Store\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function (): void {
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:auth')->name('auth.login');

    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
    Route::apiResource('stores', StoreController::class)->only(['index', 'show']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/user', fn (Request $request): UserResource => new UserResource($request->user()))->name('user');

        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::apiResource('addresses', AddressController::class)->names('addresses');
        Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show'])->names('orders');
    });
});
