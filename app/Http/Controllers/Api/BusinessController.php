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
        $businesses = Business::where('user_uuid', $request->user()->uuid)->get();

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
                'image_size_bytes' => 'nullable|integer|min:0',
                'currency' => 'nullable|string|max:3',
                'language' => 'nullable|string|max:5',
                'region' => 'nullable|string|max:5',
            ]);

            $validated['user_uuid'] = $request->user()->uuid;
            $business = Business::create($validated);

            $business->receiptSettings()->create([
                'image_template_id' => 0,
                'qrcode_data' => null,
                'footer_message' => 'Thank you',
                'include_image' => false,
                'transaction_prefix' => 'TRX-',
                'transaction_next_number' => 1,
                'label_receipt_id' => 'Receipt ID',
                'label_transaction_id' => 'Transaction ID',
                'label_date' => 'Date',
                'label_time' => 'Time',
                'label_cashier' => 'Cashier',
                'label_customer' => 'Customer',
                'label_items' => 'Items',
                'label_subtotal' => 'Subtotal',
                'label_discount' => 'Discount',
                'label_tax' => 'Tax',
                'label_total' => 'Total',
                'label_payment_method' => 'Payment Method',
                'label_amount_paid' => 'Amount Paid',
                'label_change' => 'Change',
            ]);

            $business->printerSettings()->create([
                'paper_width_mm' => 80,
                'chars_per_line' => 48,
                'encoding' => 'UTF-8',
                'feed_lines' => 3,
                'cut_enabled' => true,
                'auto_print' => false
            ]);

            $business->load('receiptSettings', 'printerSettings');

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

    public function show(Request $request, string $uuid): JsonResponse
    {
        $business = $this->findBusinessOrFail($request->user()->uuid, $uuid);

        if (!$business) {
            return $this->notFoundResponse('Business not found');
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $business
        ]);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $business = $this->findBusinessOrFail($request->user()->uuid, $uuid);

        if (!$business) {
            return $this->notFoundResponse('Business not found');
        }

        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:50',
                'image_size_bytes' => 'nullable|integer|min:0',
                'currency' => 'nullable|string|max:3',
                'language' => 'nullable|string|max:5',
                'region' => 'nullable|string|max:5',
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

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $business = $this->findBusinessOrFail($request->user()->uuid, $uuid);

        if (!$business) {
            return $this->notFoundResponse('Business not found');
        }

        $business->delete();

        return response()->json([
            'success' => true,
            'message' => 'Business deleted successfully'
        ]);
    }

    private function findBusinessOrFail(string $userUuid, string $businessUuid)
    {
        return Business::where('user_uuid', $userUuid)
            ->where('uuid', $businessUuid)
            ->first();
    }

    private function notFoundResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], 404);
    }
}
