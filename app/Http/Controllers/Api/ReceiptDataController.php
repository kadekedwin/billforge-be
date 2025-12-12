<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReceiptData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReceiptDataController extends Controller
{
    public function show(Request $request, string $businessUuid): JsonResponse
    {
        $receiptData = $this->findReceiptDataOrFail($businessUuid);

        if (!$receiptData) {
            return $this->notFoundResponse();
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $receiptData
        ]);
    }

    public function store(Request $request, string $businessUuid): JsonResponse
    {
        if (ReceiptData::where('business_uuid', $businessUuid)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This business already has receipt data'
            ], 422);
        }

        try {
            $validated = $request->validate([
                'template_id' => 'nullable|integer|min:0',
                'qrcode_data' => 'nullable|string',
                'footer_message' => 'nullable|string',
                'include_image' => 'nullable|boolean',
                'transaction_prefix' => 'nullable|string|max:10',
                'transaction_next_number' => 'nullable|integer|min:1',
            ]);

            $validated['business_uuid'] = $businessUuid;
            $receiptData = ReceiptData::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $receiptData
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function update(Request $request, string $businessUuid): JsonResponse
    {
        $receiptData = $this->findReceiptDataOrFail($businessUuid);

        if (!$receiptData) {
            return $this->notFoundResponse();
        }

        try {
            $validated = $request->validate([
                'template_id' => 'nullable|integer|min:0',
                'qrcode_data' => 'nullable|string',
                'footer_message' => 'nullable|string',
                'include_image' => 'nullable|boolean',
                'transaction_prefix' => 'nullable|string|max:10',
                'transaction_next_number' => 'nullable|integer|min:1',
            ]);

            $receiptData->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $receiptData
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(Request $request, string $businessUuid): JsonResponse
    {
        $receiptData = $this->findReceiptDataOrFail($businessUuid);

        if (!$receiptData) {
            return $this->notFoundResponse();
        }

        $receiptData->delete();

        return response()->json([
            'success' => true,
            'message' => 'Receipt data deleted successfully'
        ]);
    }

    public function updateTransactionNextNumber(Request $request, string $businessUuid): JsonResponse
    {
        $receiptData = $this->findReceiptDataOrFail($businessUuid);

        if (!$receiptData) {
            return $this->notFoundResponse();
        }

        try {
            $validated = $request->validate([
                'transaction_next_number' => 'required|integer|min:1',
            ]);

            $receiptData->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Transaction number updated successfully',
                'data' => $receiptData
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    private function findReceiptDataOrFail(string $businessUuid)
    {
        return ReceiptData::where('business_uuid', $businessUuid)->with('business')->first();
    }

    private function notFoundResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Receipt data not found'
        ], 404);
    }
}
