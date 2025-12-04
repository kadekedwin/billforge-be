<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemDiscount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemDiscountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ItemDiscount::query();

        if ($request->has('item_uuid')) {
            $query->where('item_uuid', $request->item_uuid);
        }

        $discounts = $query->get();
        return response()->json([
            'success' => true,
            'data' => $discounts
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'item_uuid' => 'required|exists:item,uuid',
                'type' => 'required|in:percentage,fixed',
                'value' => 'required|numeric|min:0',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $discount = ItemDiscount::create($validated);
            return response()->json([
                'success' => true,
                'data' => $discount
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
        $discount = ItemDiscount::find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Discount not found']
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $discount
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $discount = ItemDiscount::find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Discount not found']
            ], 404);
        }

        try {
            $validated = $request->validate([
                'item_uuid' => 'sometimes|required|exists:item,uuid',
                'type' => 'sometimes|required|in:percentage,fixed',
                'value' => 'sometimes|required|numeric|min:0',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $discount->update($validated);
            return response()->json([
                'success' => true,
                'data' => $discount
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
        $discount = ItemDiscount::find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Discount not found']
            ], 404);
        }

        $discount->delete();
        return response()->json([
            'success' => true,
            'data' => ['message' => 'Discount deleted successfully']
        ]);
    }
}
