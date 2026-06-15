<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Organization;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? today()->toDateString();

        $summary = Order::where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_sales')
            ->first();

        $byOrg = Organization::withSum(['orders as total_sales' => fn($q) => $q->where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)], 'total_amount')
            ->withCount(['orders as orders_count' => fn($q) => $q->where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)])
            ->orderBy('total_sales', 'desc')
            ->get();

        $daily = Order::where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as sales')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.super.reports.index', compact('summary', 'byOrg', 'daily', 'dateFrom', 'dateTo'));
    }
}
