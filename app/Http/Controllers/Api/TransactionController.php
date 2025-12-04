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
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_uuid' => 'required|exists:business,uuid',
                'payment_uuid' => 'nullable|exists:payment,uuid',
                'customer_name' => 'nullable|string|max:255',
                'total_amount' => 'required|numeric|min:0',
                'tax_amount' => 'required|numeric|min:0',
                'discount_amount' => 'required|numeric|min:0',
                'final_amount' => 'required|numeric|min:0',
                'status' => 'required|in:pending,paid,cancelled',
            ]);

            $transaction = Transaction::create($validated);
            return response()->json([
                'success' => true,
                'data' => $transaction
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'data' => ['errors' => $e->errors()]
            ], 422);
        }
    }

    public function show(string $id): JsonResponse
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Transaction not found']
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Transaction not found']
            ], 404);
        }

        try {
            $validated = $request->validate([
                'business_uuid' => 'sometimes|required|exists:business,uuid',
                'payment_uuid' => 'nullable|exists:payment,uuid',
                'customer_name' => 'nullable|string|max:255',
                'total_amount' => 'sometimes|required|numeric|min:0',
                'tax_amount' => 'sometimes|required|numeric|min:0',
                'discount_amount' => 'sometimes|required|numeric|min:0',
                'final_amount' => 'sometimes|required|numeric|min:0',
                'status' => 'sometimes|required|in:pending,paid,cancelled',
            ]);

            $transaction->update($validated);
            return response()->json([
                'success' => true,
                'data' => $transaction
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'data' => ['errors' => $e->errors()]
            ], 422);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Transaction not found']
            ], 404);
        }

        $transaction->delete();
        return response()->json([
            'success' => true,
            'data' => ['message' => 'Transaction deleted successfully']
        ]);
    }
}
