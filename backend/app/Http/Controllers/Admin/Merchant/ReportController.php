<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function orgId() { return auth()->user()->organization_id; }

    public function sales(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? today()->toDateString();

        $query = Order::where('organization_id', $this->orgId())
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        if ($request->filled('branch_id')) $query->where('branch_id', $request->branch_id);

        $summary = $query->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_sales, SUM(discount_amount) as total_discount, SUM(vat_amount) as total_vat')->first();

        $daily = $query->clone()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as sales')
            ->groupBy('date')->orderBy('date')->get();

        $byPayment = $query->clone()
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')->get();

        $orders = $query->clone()->with(['user', 'customer'])->latest()->paginate(20);

        $paymentLabels = [
            'cash' => 'Naqd', 'card' => 'Plastik', 'click' => 'Click',
            'payme' => 'Payme', 'humo' => 'Humo', 'uzcard' => 'Uzcard', 'debt' => 'Qarz'
        ];

        return view('admin.merchant.reports.sales', compact('summary', 'daily', 'byPayment', 'orders', 'dateFrom', 'dateTo', 'paymentLabels'));
    }

    public function products(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? today()->toDateString();

        $topProducts = OrderItem::whereHas('order', fn($q) => $q
            ->where('organization_id', $this->orgId())
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
        )->selectRaw('product_id, product_name, product_sku, SUM(quantity) as total_qty, SUM(total_amount) as total_sales, COUNT(DISTINCT order_id) as order_count')
        ->groupBy('product_id', 'product_name', 'product_sku')
        ->orderBy('total_sales', 'desc')
        ->paginate(20);

        return view('admin.merchant.reports.products', compact('topProducts', 'dateFrom', 'dateTo'));
    }

    public function shifts(Request $request)
    {
        $shifts = Shift::where('organization_id', $this->orgId())
            ->with(['user', 'terminal', 'branch'])
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('opened_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('opened_at', '<=', $request->date_to))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->latest('opened_at')
            ->paginate(20);

        return view('admin.merchant.reports.shifts', compact('shifts'));
    }

    public function cashiers(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? today()->toDateString();

        $cashiers = User::where('organization_id', $this->orgId())
            ->whereIn('role', ['cashier', 'org_admin'])
            ->withCount(['orders as orders_count' => fn($q) => $q->where('status', 'completed')
                ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)])
            ->withSum(['orders as total_sales' => fn($q) => $q->where('status', 'completed')
                ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)], 'total_amount')
            ->get();

        return view('admin.merchant.reports.cashiers', compact('cashiers', 'dateFrom', 'dateTo'));
    }

    public function stock(Request $request)
    {
        return redirect()->route('merchant.stocks.index');
    }
}
