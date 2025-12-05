<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentMethodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PaymentMethod::query();

        if ($request->has('business_uuid')) {
            $query->where('business_uuid', $request->business_uuid);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $payments
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_uuid' => 'required|exists:business,uuid',
                'name' => 'required|string|max:255',
            ]);

            $paymentMethod = PaymentMethod::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $paymentMethod
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
        $paymentMethod = PaymentMethod::find($id);

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Payment method not found']
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $paymentMethod
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $paymentMethod = PaymentMethod::find($id);

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'business_uuid' => 'sometimes|required|exists:business,uuid',
                'name' => 'sometimes|required|string|max:255',
            ]);

            $paymentMethod->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $paymentMethod
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
        $paymentMethod = PaymentMethod::find($id);

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found'
            ], 404);
        }

        $paymentMethod->delete();
        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully'
        ]);
    }
}
