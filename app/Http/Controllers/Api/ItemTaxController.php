<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemTax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemTaxController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ItemTax::query();

        if ($request->has('business_uuid')) {
            $query->where('business_uuid', $request->business_uuid);
        }

        $taxes = $query->get();
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $taxes
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_uuid' => 'required|exists:business,uuid',
                'name' => 'required|string|max:100',
                'rate' => 'required|numeric|min:0|max:100',
            ]);

            $tax = ItemTax::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $tax
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
        $tax = ItemTax::find($id);

        if (!$tax) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Tax not found']
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $tax
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $tax = ItemTax::find($id);

        if (!$tax) {
            return response()->json([
                'success' => false,
                'message' => 'Tax not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'business_uuid' => 'sometimes|required|exists:business,uuid',
                'name' => 'sometimes|required|string|max:100',
                'rate' => 'sometimes|required|numeric|min:0|max:100',
            ]);

            $tax->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $tax
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
        $tax = ItemTax::find($id);

        if (!$tax) {
            return response()->json([
                'success' => false,
                'message' => 'Tax not found'
            ], 404);
        }

        $tax->delete();
        return response()->json([
            'success' => true,
            'message' => 'Tax deleted successfully'
        ]);
    }
}
