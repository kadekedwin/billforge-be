<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BusinessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $businesses = Business::where('user_uuid', $user->uuid)->get();
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $businesses
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:50',
            ]);

            $validated['user_uuid'] = $request->user()->uuid;
            $business = Business::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $business
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $business = Business::where('user_uuid', $user->uuid)->find($id);

        if (!$business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $business
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $business = Business::where('user_uuid', $user->uuid)->find($id);

        if (!$business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:50',
            ]);

            $business->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $business
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $business = Business::where('user_uuid', $user->uuid)->find($id);

        if (!$business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found'
            ], 404);
        }

        $business->delete();
        return response()->json([
            'success' => true,
            'message' => 'Business deleted successfully'
        ]);
    }
}
