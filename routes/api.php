<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ItemDiscountController;
use App\Http\Controllers\Api\ItemTaxController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransactionItemController;
use \App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);

    Route::get('/users', [UserController::class, 'index']);

    Route::middleware('verified')->group(function () {
        Route::put('/users', [UserController::class, 'update']);

        Route::apiResource('businesses', BusinessController::class);
        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('items', ItemController::class);
        Route::apiResource('item-taxes', ItemTaxController::class);
        Route::apiResource('item-discounts', ItemDiscountController::class);
        Route::apiResource('transactions', TransactionController::class);
        Route::apiResource('transaction-items', TransactionItemController::class);
        Route::apiResource('payment-methods', PaymentMethodController::class);
    });
});
