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
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ItemCategoryController;
use \App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReceiptDataController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:3,1');
Route::post('/forgot-password-reset', [AuthController::class, 'forgotPasswordReset']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/users', [UserController::class, 'index']);

    Route::middleware('verified')->group(function () {
        Route::put('/users', [UserController::class, 'update']);

        Route::apiResource('businesses', BusinessController::class);
        Route::get('businesses/{business_uuid}/receipt-data', [ReceiptDataController::class, 'show']);
        Route::post('businesses/{business_uuid}/receipt-data', [ReceiptDataController::class, 'store']);
        Route::patch('businesses/{business_uuid}/receipt-data', [ReceiptDataController::class, 'update']);
        Route::delete('businesses/{business_uuid}/receipt-data', [ReceiptDataController::class, 'destroy']);
        Route::patch('businesses/{business_uuid}/receipt-data/transaction-next-number', [ReceiptDataController::class, 'updateTransactionNextNumber']);
        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('items', ItemController::class);
        Route::get('items/{item_uuid}/categories', [ItemCategoryController::class, 'index']);
        Route::post('items/{item_uuid}/categories', [ItemCategoryController::class, 'store']);
        Route::delete('items/{item_uuid}/categories/{category_uuid}', [ItemCategoryController::class, 'destroy']);
        Route::apiResource('item-taxes', ItemTaxController::class);
        Route::apiResource('item-discounts', ItemDiscountController::class);
        Route::apiResource('transactions', TransactionController::class);
        Route::apiResource('transaction-items', TransactionItemController::class);
        Route::apiResource('payment-methods', PaymentMethodController::class);
    });
});
