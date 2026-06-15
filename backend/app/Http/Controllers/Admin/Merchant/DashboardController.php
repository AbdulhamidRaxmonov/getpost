<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Stock;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $orgId = auth()->user()->organization_id;

        $stats = [
            'today_orders' => Order::where('organization_id', $orgId)->where('status', 'completed')->whereDate('created_at', today())->count(),
            'today_sales' => Order::where('organization_id', $orgId)->where('status', 'completed')->whereDate('created_at', today())->sum('total_amount'),
            'month_sales' => Order::where('organization_id', $orgId)->where('status', 'completed')->where('created_at', '>=', now()->startOfMonth())->sum('total_amount'),
            'month_orders' => Order::where('organization_id', $orgId)->where('status', 'completed')->where('created_at', '>=', now()->startOfMonth())->count(),
            'total_products' => Product::where('organization_id', $orgId)->where('is_active', true)->count(),
            'active_shifts' => Shift::where('organization_id', $orgId)->where('status', 'open')->count(),
            'low_stock' => Stock::whereHas('product', fn($q) => $q->where('organization_id', $orgId)->where('is_active', true))->whereColumn('quantity', '<=', 'min_quantity')->count(),
        ];

        $salesChart = Order::where('organization_id', $orgId)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byPayment = Order::where('organization_id', $orgId)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $recentOrders = Order::where('organization_id', $orgId)
            ->with(['user', 'items'])
            ->latest()
            ->take(8)
            ->get();

        $topProducts = \App\Models\OrderItem::whereHas('order', fn($q) => $q->where('organization_id', $orgId)->where('status', 'completed')->whereDate('created_at', today()))
            ->selectRaw('product_name, SUM(quantity) as qty, SUM(total_amount) as total')
            ->groupBy('product_name')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        return view('admin.merchant.dashboard', compact('stats', 'salesChart', 'byPayment', 'recentOrders', 'topProducts'));
    }
}
