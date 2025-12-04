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

        if ($request->has('business_uuid')) {
            $query->where('business_uuid', $request->business_uuid);
        }

        $discounts = $query->get();
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $discounts
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_uuid' => 'required|exists:business,uuid',
                'name' => 'required|string|max:100',
                'type' => 'required|in:percentage,fixed',
                'value' => 'required|numeric|min:0',
            ]);

            $discount = ItemDiscount::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $discount
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
        $discount = ItemDiscount::find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Discount not found']
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $discount
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $discount = ItemDiscount::find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Discount not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'business_uuid' => 'sometimes|required|exists:business,uuid',
                'name' => 'sometimes|required|string|max:100',
                'type' => 'sometimes|required|in:percentage,fixed',
                'value' => 'sometimes|required|numeric|min:0',
            ]);

            $discount->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $discount
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
        $discount = ItemDiscount::find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Discount not found'
            ], 404);
        }

        $discount->delete();
        return response()->json([
            'success' => true,
            'message' => 'Discount deleted successfully'
        ]);
    }
}
