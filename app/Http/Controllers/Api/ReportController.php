<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function salesSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'business_uuid' => 'required|uuid|exists:business,uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = Transaction::where('business_uuid', $request->business_uuid);

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $summary = $query->selectRaw('
            COUNT(*) as total_transactions,
            SUM(final_amount) as total_sales,
            SUM(tax_amount) as total_tax,
            SUM(discount_amount) as total_discounts,
            AVG(final_amount) as average_transaction_value
        ')->first();

        $totalItemsSold = TransactionItem::whereIn('transaction_uuid', function ($q) use ($request) {
            $q->select('uuid')
                ->from('transaction')
                ->where('business_uuid', $request->business_uuid);

            if ($request->start_date) {
                $q->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $q->whereDate('created_at', '<=', $request->end_date);
            }
        })->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => [
                'total_transactions' => (int) $summary->total_transactions,
                'total_sales' => (float) $summary->total_sales ?? 0,
                'total_tax' => (float) $summary->total_tax ?? 0,
                'total_discounts' => (float) $summary->total_discounts ?? 0,
                'average_transaction_value' => (float) $summary->average_transaction_value ?? 0,
                'total_items_sold' => (int) $totalItemsSold,
            ]
        ]);
    }

    public function salesByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'business_uuid' => 'required|uuid|exists:business,uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = Transaction::where('business_uuid', $request->business_uuid);

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $salesByDate = $query->selectRaw('
            DATE(created_at) as date,
            COUNT(*) as transaction_count,
            SUM(final_amount) as total_amount
        ')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'transaction_count' => (int) $item->transaction_count,
                    'total_amount' => (float) $item->total_amount,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $salesByDate
        ]);
    }

    public function salesByItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'business_uuid' => 'required|uuid|exists:business,uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $salesByItem = TransactionItem::select(
            'transaction_item.name',
            'transaction_item.sku',
            DB::raw('SUM(transaction_item.quantity) as quantity_sold'),
            DB::raw('SUM(transaction_item.total_price) as total_revenue')
        )
            ->join('transaction', 'transaction_item.transaction_uuid', '=', 'transaction.uuid')
            ->where('transaction.business_uuid', $request->business_uuid);

        if ($request->start_date) {
            $salesByItem->whereDate('transaction.created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $salesByItem->whereDate('transaction.created_at', '<=', $request->end_date);
        }

        $salesByItem = $salesByItem
            ->groupBy('transaction_item.name', 'transaction_item.sku')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'quantity_sold' => (int) $item->quantity_sold,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $salesByItem
        ]);
    }

    public function salesByCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'business_uuid' => 'required|uuid|exists:business,uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get sales data with category information using item_uuid
        $salesByCategory = TransactionItem::select(
            'categories.name as category_name',
            DB::raw('SUM(transaction_item.quantity) as quantity_sold'),
            DB::raw('SUM(transaction_item.total_price) as total_revenue')
        )
            ->join('transaction', 'transaction_item.transaction_uuid', '=', 'transaction.uuid')
            ->leftJoin('item', 'transaction_item.item_uuid', '=', 'item.uuid')
            ->leftJoin('item_categories', 'item.uuid', '=', 'item_categories.item_uuid')
            ->leftJoin('categories', 'item_categories.category_uuid', '=', 'categories.uuid')
            ->where('transaction.business_uuid', $request->business_uuid);

        if ($request->start_date) {
            $salesByCategory->whereDate('transaction.created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $salesByCategory->whereDate('transaction.created_at', '<=', $request->end_date);
        }

        $salesByCategory = $salesByCategory
            ->groupBy('categories.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($item) {
                return [
                    'category_name' => $item->category_name ?? 'Uncategorized',
                    'quantity_sold' => (int) $item->quantity_sold,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $salesByCategory
        ]);
    }

    public function salesByPaymentMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'business_uuid' => 'required|uuid|exists:business,uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = Transaction::select(
            'payment_method.name as payment_method_name',
            DB::raw('COUNT(*) as transaction_count'),
            DB::raw('SUM(transaction.final_amount) as total_amount')
        )
            ->leftJoin('payment_method', 'transaction.payment_method_uuid', '=', 'payment_method.uuid')
            ->where('transaction.business_uuid', $request->business_uuid);

        if ($request->start_date) {
            $query->whereDate('transaction.created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('transaction.created_at', '<=', $request->end_date);
        }

        $salesByPaymentMethod = $query
            ->groupBy('payment_method.name')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($item) {
                return [
                    'payment_method_name' => $item->payment_method_name ?? 'Cash/Unspecified',
                    'transaction_count' => (int) $item->transaction_count,
                    'total_amount' => (float) $item->total_amount,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $salesByPaymentMethod
        ]);
    }
}
