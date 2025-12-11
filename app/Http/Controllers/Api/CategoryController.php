<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        if ($request->has('business_uuid')) {
            $query->where('business_uuid', $request->business_uuid);
        } else {
            $query->where('user_uuid', $request->user()->uuid);
        }

        $categories = $query->get();
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $categories
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_uuid' => 'required|exists:business,uuid',
                'name' => 'required|string|max:255',
            ]);

            $validated['user_uuid'] = $request->user()->uuid;

            $category = Category::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        $category = Category::where('uuid', $uuid)->first();

        if (!$category || $category->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $category
        ]);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $category = Category::where('uuid', $uuid)->first();

        if (!$category || $category->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $category->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $category = Category::where('uuid', $uuid)->first();

        if (!$category || $category->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->delete();
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
