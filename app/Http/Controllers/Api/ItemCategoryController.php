<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemCategoryController extends Controller
{
    public function index(Request $request, string $itemUuid): JsonResponse
    {
        $item = Item::where('uuid', $itemUuid)->first();

        if (!$item || $item->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $categories = $item->categories;
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $categories
        ]);
    }

    public function store(Request $request, string $itemUuid): JsonResponse
    {
        $item = Item::where('uuid', $itemUuid)->first();

        if (!$item || $item->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'category_uuid' => 'required|exists:categories,uuid',
            ]);

            $category = Category::where('uuid', $validated['category_uuid'])->first();

            if (!$category || $category->user_uuid !== $request->user()->uuid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            if (!$item->categories()->where('category_uuid', $validated['category_uuid'])->exists()) {
                $item->categories()->attach($validated['category_uuid']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category attached to item successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(Request $request, string $itemUuid, string $categoryUuid): JsonResponse
    {
        $item = Item::where('uuid', $itemUuid)->first();

        if (!$item || $item->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $category = Category::where('uuid', $categoryUuid)->first();

        if (!$category || $category->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $item->categories()->detach($categoryUuid);

        return response()->json([
            'success' => true,
            'message' => 'Category detached from item successfully'
        ]);
    }
}
