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

    public function show(string $id): JsonResponse
    {
        $transactionItem = TransactionItem::find($id);

        if (!$transactionItem) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Transaction item not found']
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $transactionItem
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $transactionItem = TransactionItem::find($id);

        if (!$transactionItem) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction item not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'transaction_uuid' => 'sometimes|required|exists:transaction,uuid',
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

    public function destroy(string $id): JsonResponse
    {
        $transactionItem = TransactionItem::find($id);

        if (!$transactionItem) {
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
