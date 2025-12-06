<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TransactionItem::query();

        if ($request->has('transaction_uuid')) {
            $query->where('transaction_uuid', $request->transaction_uuid);
        } else {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->where('user_uuid', $request->user()->uuid);
            });
        }

        $transactionItems = $query->get();
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $transactionItems
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'transaction_uuid' => 'required|exists:transaction,uuid',
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'quantity' => 'required|integer|min:1',
                'base_price' => 'required|numeric|min:0',
                'discount_amount' => 'required|numeric|min:0',
                'tax_amount' => 'required|numeric|min:0',
                'total_price' => 'required|numeric|min:0',
            ]);

            $transactionItem = TransactionItem::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $transactionItem
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
        $transactionItem = TransactionItem::where('uuid', $uuid)->with('transaction')->first();

        if (!$transactionItem || $transactionItem->transaction->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction item not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $transactionItem
        ]);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $transactionItem = TransactionItem::where('uuid', $uuid)->with('transaction')->first();

        if (!$transactionItem || $transactionItem->transaction->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction item not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'transaction_uuid' => 'sometimes|required|exists:transaction,uuid',
                'name' => 'sometimes|required|string|max:255',
                'sku' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'quantity' => 'sometimes|required|integer|min:1',
                'base_price' => 'sometimes|required|numeric|min:0',
                'discount_amount' => 'sometimes|required|numeric|min:0',
                'tax_amount' => 'sometimes|required|numeric|min:0',
                'total_price' => 'sometimes|required|numeric|min:0',
            ]);

            $transactionItem->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $transactionItem
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
        $transactionItem = TransactionItem::where('uuid', $uuid)->with('transaction')->first();

        if (!$transactionItem || $transactionItem->transaction->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction item not found'
            ], 404);
        }

        $transactionItem->delete();
        return response()->json([
            'success' => true,
            'message' => 'Transaction item deleted successfully'
        ]);
    }
}
