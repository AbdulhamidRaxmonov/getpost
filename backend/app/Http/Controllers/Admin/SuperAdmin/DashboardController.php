<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\Order;
use App\Models\Shift;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_organizations' => Organization::count(),
            'active_organizations' => Organization::where('is_active', true)->count(),
            'total_users' => User::count(),
            'today_orders' => Order::whereDate('created_at', today())->where('status', 'completed')->count(),
            'today_sales' => Order::whereDate('created_at', today())->where('status', 'completed')->sum('total_amount'),
            'month_sales' => Order::where('created_at', '>=', now()->startOfMonth())->where('status', 'completed')->sum('total_amount'),
            'active_shifts' => Shift::where('status', 'open')->count(),
        ];

        $recentOrgs = Organization::latest()->take(5)->get();

        $salesChart = Order::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.super.dashboard', compact('stats', 'recentOrgs', 'salesChart'));
    }
}
