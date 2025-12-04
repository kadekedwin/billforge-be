<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::query();

        if ($request->has('transaction_uuid')) {
            $query->where('transaction_uuid', $request->transaction_uuid);
        }

        if ($request->has('method')) {
            $query->where('method', $request->method);
        }

        $payments = $query->orderBy('paid_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'transaction_uuid' => 'required|exists:transaction,uuid',
                'method' => 'required|string|max:100',
                'amount' => 'required|numeric|min:0',
                'paid_at' => 'nullable|date',
            ]);

            $payment = Payment::create($validated);
            return response()->json([
                'success' => true,
                'data' => $payment
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
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Payment not found']
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Payment not found']
            ], 404);
        }

        try {
            $validated = $request->validate([
                'transaction_uuid' => 'sometimes|required|exists:transaction,uuid',
                'method' => 'sometimes|required|string|max:100',
                'amount' => 'sometimes|required|numeric|min:0',
                'paid_at' => 'nullable|date',
            ]);

            $payment->update($validated);
            return response()->json([
                'success' => true,
                'data' => $payment
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
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Payment not found']
            ], 404);
        }

        $payment->delete();
        return response()->json([
            'success' => true,
            'data' => ['message' => 'Payment deleted successfully']
        ]);
    }
}
