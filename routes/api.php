<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\RecoveryApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile App API (v1)
|--------------------------------------------------------------------------
| Auth: Bearer token (Laravel Sanctum)
| Base: /api/v1
*/

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        // New Order
        Route::get('orders/meta', [OrderApiController::class, 'formMeta']);
        Route::get('orders/pending', [OrderApiController::class, 'pending']);
        Route::get('orders/customers', [OrderApiController::class, 'searchCustomers']);
        Route::get('orders/products', [OrderApiController::class, 'searchProducts']);
        Route::get('orders/products/{productCode}', [OrderApiController::class, 'productDetails']);
        Route::get('orders/{orderNo}', [OrderApiController::class, 'show']);
        Route::post('orders', [OrderApiController::class, 'store']);
        Route::put('orders/{orderNo}', [OrderApiController::class, 'update']);

        // New Customer
        Route::get('customers/areas', [CustomerApiController::class, 'areas']);
        Route::get('customers/categories', [CustomerApiController::class, 'categories']);
        Route::get('customers/lookup', [CustomerApiController::class, 'lookup']);
        Route::post('customers', [CustomerApiController::class, 'store']);

        // Market Recovery
        Route::get('recovery/meta', [RecoveryApiController::class, 'meta']);
        Route::get('recovery/area/{areaCode}/customers', [RecoveryApiController::class, 'customersByArea']);
        Route::post('recovery', [RecoveryApiController::class, 'store']);
        Route::post('recovery/cheque', [RecoveryApiController::class, 'storeCheque']);
    });
});
