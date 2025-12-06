<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::query();

        if ($request->has('business_uuid')) {
            $query->where('business_uuid', $request->business_uuid);
        } else {
            $query->where('user_uuid', $request->user()->uuid);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $transactions
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_uuid' => 'required|exists:business,uuid',
                'payment_method_uuid' => 'nullable|exists:payment_method,uuid',
                'customer_uuid' => 'nullable|exists:customer,uuid',
                'total_amount' => 'required|numeric|min:0',
                'tax_amount' => 'required|numeric|min:0',
                'discount_amount' => 'required|numeric|min:0',
                'final_amount' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $validated['user_uuid'] = $request->user()->uuid;
            $transaction = Transaction::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $transaction
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        $transaction = Transaction::where('uuid', $uuid)->first();

        if (!$transaction || $transaction->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $transaction
        ]);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $transaction = Transaction::where('uuid', $uuid)->first();

        if (!$transaction || $transaction->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'business_uuid' => 'sometimes|required|exists:business,uuid',
                'payment_method_uuid' => 'nullable|exists:payment_method,uuid',
                'customer_uuid' => 'nullable|exists:customer,uuid',
                'total_amount' => 'sometimes|required|numeric|min:0',
                'tax_amount' => 'sometimes|required|numeric|min:0',
                'discount_amount' => 'sometimes|required|numeric|min:0',
                'final_amount' => 'sometimes|required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $transaction->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $transaction
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $transaction = Transaction::where('uuid', $uuid)->first();

        if (!$transaction || $transaction->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        $transaction->delete();
        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully'
        ]);
    }
}
