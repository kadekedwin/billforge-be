<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($request->has('business_uuid')) {
            $query->where('business_uuid', $request->business_uuid);
        } else {
            $query->where('user_uuid', $request->user()->uuid);
        }

        $customers = $query->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $customers
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_uuid' => 'required|exists:business,uuid',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:50',
            ]);

            $validated['user_uuid'] = $request->user()->uuid;
            $customer = Customer::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $customer
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
        $customer = Customer::where('uuid', $uuid)->first();

        if (!$customer || $customer->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $customer
        ]);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $customer = Customer::where('uuid', $uuid)->first();

        if (!$customer || $customer->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'business_uuid' => 'sometimes|required|exists:business,uuid',
                'name' => 'sometimes|required|string|max:255',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:50',
            ]);

            $customer->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $customer
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
        $customer = Customer::where('uuid', $uuid)->first();

        if (!$customer || $customer->user_uuid !== $request->user()->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $customer->delete();
        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully'
        ]);
    }
}
