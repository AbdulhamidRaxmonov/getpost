<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Kunlik sotuv hisoboti
    public function dailySales(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
            'branch_id' => 'nullable|integer',
        ]);

        $date = $request->date ?? today()->toDateString();
        $orgId = $request->user()->organization_id;

        $query = Order::where('organization_id', $orgId)
            ->where('status', 'completed')
            ->whereDate('created_at', $date);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $summary = $query->selectRaw('
            COUNT(*) as total_orders,
            SUM(total_amount) as total_sales,
            SUM(discount_amount) as total_discount,
            SUM(vat_amount) as total_vat,
            AVG(total_amount) as avg_order
        ')->first();

        $byPayment = $query->clone()
            ->selectRaw('payment_method, SUM(total_amount) as amount, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get();

        $hourly = $query->clone()
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as orders, SUM(total_amount) as sales')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return response()->json([
            'date' => $date,
            'summary' => $summary,
            'by_payment' => $byPayment,
            'hourly' => $hourly,
        ]);
    }

    // Davriy sotuv hisoboti
    public function periodSales(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'branch_id' => 'nullable|integer',
        ]);

        $orgId = $request->user()->organization_id;

        $query = Order::where('organization_id', $orgId)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $request->date_from)
            ->whereDate('created_at', '<=', $request->date_to);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $summary = $query->clone()->selectRaw('
            COUNT(*) as total_orders,
            SUM(total_amount) as total_sales,
            SUM(discount_amount) as total_discount,
            SUM(vat_amount) as total_vat
        ')->first();

        $daily = $query->clone()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as sales')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byPayment = $query->clone()
            ->selectRaw('payment_method, SUM(total_amount) as amount, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'summary' => $summary,
            'daily' => $daily,
            'by_payment' => $byPayment,
        ]);
    }

    // Eng ko'p sotiladigan mahsulotlar
    public function topProducts(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $orgId = $request->user()->organization_id;
        $limit = $request->limit ?? 20;

        $query = OrderItem::whereHas('order', function ($q) use ($orgId, $request) {
            $q->where('organization_id', $orgId)
              ->where('status', 'completed');
            if ($request->filled('date_from')) {
                $q->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $q->whereDate('created_at', '<=', $request->date_to);
            }
        });

        $topProducts = $query->selectRaw('
            product_id,
            product_name,
            product_sku,
            SUM(quantity) as total_quantity,
            SUM(total_amount) as total_sales,
            COUNT(DISTINCT order_id) as order_count
        ')
        ->groupBy('product_id', 'product_name', 'product_sku')
        ->orderBy('total_sales', 'desc')
        ->limit($limit)
        ->get();

        return response()->json($topProducts);
    }

    // Ombor holati
    public function stockReport(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $stocks = Product::with(['category', 'unit', 'stocks' => function ($q) use ($request) {
            if ($request->filled('branch_id')) {
                $q->where('branch_id', $request->branch_id);
            }
        }])
        ->where('organization_id', $orgId)
        ->where('is_active', true)
        ->where('track_stock', true)
        ->get();

        return response()->json($stocks);
    }

    // Dashboard uchun umumiy statistika
    public function dashboard(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $today = today();
        $thisMonth = now()->startOfMonth();

        $todaySales = Order::where('organization_id', $orgId)
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $todayOrders = Order::where('organization_id', $orgId)
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->count();

        $monthSales = Order::where('organization_id', $orgId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $thisMonth)
            ->sum('total_amount');

        $monthOrders = Order::where('organization_id', $orgId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $thisMonth)
            ->count();

        $lowStockProducts = Product::with('stocks')
            ->where('organization_id', $orgId)
            ->where('is_active', true)
            ->where('track_stock', true)
            ->whereHas('stocks', function ($q) {
                $q->whereColumn('quantity', '<=', 'min_quantity');
            })
            ->count();

        return response()->json([
            'today_sales' => $todaySales,
            'today_orders' => $todayOrders,
            'month_sales' => $monthSales,
            'month_orders' => $monthOrders,
            'low_stock_count' => $lowStockProducts,
        ]);
    }
}
