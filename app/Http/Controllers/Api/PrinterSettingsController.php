<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrinterSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PrinterSettingsController extends Controller
{
    public function show(Request $request, string $businessUuid): JsonResponse
    {
        $printerSettings = $this->findPrinterSettingsOrFail($businessUuid);

        if (!$printerSettings) {
            return $this->notFoundResponse();
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $printerSettings
        ]);
    }

    public function store(Request $request, string $businessUuid): JsonResponse
    {
        if (PrinterSettings::where('business_uuid', $businessUuid)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This business already has printer settings'
            ], 422);
        }

        try {
            $validated = $request->validate([
                'paper_width_mm' => 'required|integer|min:1',
                'chars_per_line' => 'required|integer|min:1',
                'encoding' => 'required|string|max:50',
                'feed_lines' => 'nullable|integer|min:0',
                'cut_enabled' => 'nullable|boolean',
                'auto_print' => 'nullable|boolean',
            ]);

            $validated['business_uuid'] = $businessUuid;
            $printerSettings = PrinterSettings::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $printerSettings
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
        $printerSettings = $this->findPrinterSettingsOrFail($businessUuid);

        if (!$printerSettings) {
            return $this->notFoundResponse();
        }

        try {
            $validated = $request->validate([
                'paper_width_mm' => 'nullable|integer|min:1',
                'chars_per_line' => 'nullable|integer|min:1',
                'encoding' => 'nullable|string|max:50',
                'feed_lines' => 'nullable|integer|min:0',
                'cut_enabled' => 'nullable|boolean',
                'auto_print' => 'nullable|boolean',
            ]);

            $printerSettings->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $printerSettings
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
        $printerSettings = $this->findPrinterSettingsOrFail($businessUuid);

        if (!$printerSettings) {
            return $this->notFoundResponse();
        }

        $printerSettings->delete();

        return response()->json([
            'success' => true,
            'message' => 'Printer settings deleted successfully'
        ]);
    }

    private function findPrinterSettingsOrFail(string $businessUuid)
    {
        return PrinterSettings::where('business_uuid', $businessUuid)->with('business')->first();
    }

    private function notFoundResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Printer settings not found'
        ], 404);
    }
}
