<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReceiptSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReceiptSettingsController extends Controller
{
    public function show(Request $request, string $businessUuid): JsonResponse
    {
        $receiptSettings = $this->findReceiptSettingsOrFail($businessUuid);

        if (!$receiptSettings) {
            return $this->notFoundResponse();
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $receiptSettings
        ]);
    }

    public function store(Request $request, string $businessUuid): JsonResponse
    {
        if (ReceiptSettings::where('business_uuid', $businessUuid)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This business already has receipt settings'
            ], 422);
        }

        try {
            $validated = $request->validate([
                'receipt_style_id' => 'nullable|integer|min:0',
                'qrcode_data' => 'nullable|string',
                'footer_message' => 'nullable|string',
                'include_image' => 'nullable|boolean',
                'transaction_prefix' => 'nullable|string|max:10',
                'transaction_next_number' => 'nullable|integer|min:1',
                'label_receipt_id' => 'nullable|string|max:100',
                'label_receipt_id_enabled' => 'nullable|boolean',
                'label_transaction_id' => 'nullable|string|max:100',
                'label_transaction_id_enabled' => 'nullable|boolean',
                'label_date' => 'nullable|string|max:100',
                'label_date_enabled' => 'nullable|boolean',
                'label_time' => 'nullable|string|max:100',
                'label_time_enabled' => 'nullable|boolean',
                'label_cashier' => 'nullable|string|max:100',
                'label_cashier_enabled' => 'nullable|boolean',
                'label_customer' => 'nullable|string|max:100',
                'label_customer_enabled' => 'nullable|boolean',
                'label_items' => 'nullable|string|max:100',
                'label_items_enabled' => 'nullable|boolean',
                'label_subtotal' => 'nullable|string|max:100',
                'label_subtotal_enabled' => 'nullable|boolean',
                'label_discount' => 'nullable|string|max:100',
                'label_discount_enabled' => 'nullable|boolean',
                'label_tax' => 'nullable|string|max:100',
                'label_tax_enabled' => 'nullable|boolean',
                'label_total' => 'nullable|string|max:100',
                'label_total_enabled' => 'nullable|boolean',
                'label_payment_method' => 'nullable|string|max:100',
                'label_payment_method_enabled' => 'nullable|boolean',
                'label_amount_paid' => 'nullable|string|max:100',
                'label_amount_paid_enabled' => 'nullable|boolean',
                'label_change' => 'nullable|string|max:100',
                'label_change_enabled' => 'nullable|boolean',
                'font' => 'nullable|string|max:50',
                'line_character' => 'nullable|string|max:5',
                'item_layout' => 'nullable|integer',
            ]);

            $validated['business_uuid'] = $businessUuid;
            $receiptSettings = ReceiptSettings::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $receiptSettings
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
        $receiptSettings = $this->findReceiptSettingsOrFail($businessUuid);

        if (!$receiptSettings) {
            return $this->notFoundResponse();
        }

        try {
            $validated = $request->validate([
                'receipt_style_id' => 'nullable|integer|min:0',
                'qrcode_data' => 'nullable|string',
                'footer_message' => 'nullable|string',
                'include_image' => 'nullable|boolean',
                'transaction_prefix' => 'nullable|string|max:10',
                'transaction_next_number' => 'nullable|integer|min:1',
                'label_receipt_id' => 'nullable|string|max:100',
                'label_receipt_id_enabled' => 'nullable|boolean',
                'label_transaction_id' => 'nullable|string|max:100',
                'label_transaction_id_enabled' => 'nullable|boolean',
                'label_date' => 'nullable|string|max:100',
                'label_date_enabled' => 'nullable|boolean',
                'label_time' => 'nullable|string|max:100',
                'label_time_enabled' => 'nullable|boolean',
                'label_cashier' => 'nullable|string|max:100',
                'label_cashier_enabled' => 'nullable|boolean',
                'label_customer' => 'nullable|string|max:100',
                'label_customer_enabled' => 'nullable|boolean',
                'label_items' => 'nullable|string|max:100',
                'label_items_enabled' => 'nullable|boolean',
                'label_subtotal' => 'nullable|string|max:100',
                'label_subtotal_enabled' => 'nullable|boolean',
                'label_discount' => 'nullable|string|max:100',
                'label_discount_enabled' => 'nullable|boolean',
                'label_tax' => 'nullable|string|max:100',
                'label_tax_enabled' => 'nullable|boolean',
                'label_total' => 'nullable|string|max:100',
                'label_total_enabled' => 'nullable|boolean',
                'label_payment_method' => 'nullable|string|max:100',
                'label_payment_method_enabled' => 'nullable|boolean',
                'label_amount_paid' => 'nullable|string|max:100',
                'label_amount_paid_enabled' => 'nullable|boolean',
                'label_change' => 'nullable|string|max:100',
                'label_change_enabled' => 'nullable|boolean',
                'font' => 'nullable|string|max:50',
                'line_character' => 'nullable|string|max:5',
                'item_layout' => 'nullable|integer',
            ]);

            $receiptSettings->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $receiptSettings
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
        $receiptSettings = $this->findReceiptSettingsOrFail($businessUuid);

        if (!$receiptSettings) {
            return $this->notFoundResponse();
        }

        $receiptSettings->delete();

        return response()->json([
            'success' => true,
            'message' => 'Receipt settings deleted successfully'
        ]);
    }

    public function updateTransactionNextNumber(Request $request, string $businessUuid): JsonResponse
    {
        $receiptSettings = $this->findReceiptSettingsOrFail($businessUuid);

        if (!$receiptSettings) {
            return $this->notFoundResponse();
        }

        try {
            $validated = $request->validate([
                'transaction_next_number' => 'required|integer|min:1',
            ]);

            $receiptSettings->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Transaction number updated successfully',
                'data' => $receiptSettings
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    private function findReceiptSettingsOrFail(string $businessUuid)
    {
        return ReceiptSettings::where('business_uuid', $businessUuid)->with('business')->first();
    }

    private function notFoundResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Receipt settings not found'
        ], 404);
    }
}
