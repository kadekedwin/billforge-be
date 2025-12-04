<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Item::with(['tax', 'discount']);

        if ($request->has('business_uuid')) {
            $query->where('business_uuid', $request->business_uuid);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $items = $query->get();
        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_uuid' => 'required|exists:business,uuid',
                'discount_uuid' => 'nullable|exists:item_discount,uuid',
                'tax_uuid' => 'nullable|exists:item_tax,uuid',
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'base_price' => 'required|numeric|min:0',
                'is_active' => 'boolean',
            ]);

            $item = Item::create($validated);
            return response()->json([
                'success' => true,
                'data' => $item->load(['tax', 'discount'])
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
        $item = Item::with(['tax', 'discount'])->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Item not found']
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $item
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Item not found']
            ], 404);
        }

        try {
            $validated = $request->validate([
                'business_uuid' => 'sometimes|required|exists:business,uuid',
                'discount_uuid' => 'nullable|exists:item_discount,uuid',
                'tax_uuid' => 'nullable|exists:item_tax,uuid',
                'name' => 'sometimes|required|string|max:255',
                'sku' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'base_price' => 'sometimes|required|numeric|min:0',
                'is_active' => 'boolean',
            ]);

            $item->update($validated);
            return response()->json([
                'success' => true,
                'data' => $item->load(['tax', 'discount'])
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
        $item = Item::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Item not found']
            ], 404);
        }

        $item->delete();
        return response()->json([
            'success' => true,
            'data' => ['message' => 'Item deleted successfully']
        ]);
    }
}
