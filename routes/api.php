<?php

use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ItemDiscountController;
use App\Http\Controllers\Api\ItemTaxController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransactionItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    
    Route::apiResource('businesses', BusinessController::class);
    Route::apiResource('items', ItemController::class);
    Route::apiResource('item-taxes', ItemTaxController::class);
    Route::apiResource('item-discounts', ItemDiscountController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('transaction-items', TransactionItemController::class);
    Route::apiResource('payment-methods', PaymentMethodController::class);
});
