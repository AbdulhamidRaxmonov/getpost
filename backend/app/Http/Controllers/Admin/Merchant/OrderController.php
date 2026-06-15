<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private function orgId() { return auth()->user()->organization_id; }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'customer', 'terminal'])
            ->where('organization_id', $this->orgId());

        if ($request->filled('search')) {
            $query->where('receipt_number', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('branch_id')) $query->where('branch_id', $request->branch_id);

        $orders = $query->latest()->paginate(20);
        $paymentLabels = ['cash' => 'Naqd', 'card' => 'Plastik', 'click' => 'Click', 'payme' => 'Payme', 'humo' => 'Humo', 'uzcard' => 'Uzcard', 'debt' => 'Qarz'];

        return view('admin.merchant.orders.index', compact('orders', 'paymentLabels'));
    }

    public function show(Order $order)
    {
        if ($order->organization_id !== $this->orgId()) abort(403);
        $order->load(['items.product', 'user', 'customer', 'terminal', 'branch', 'shift']);
        $paymentLabels = ['cash' => 'Naqd', 'card' => 'Plastik', 'click' => 'Click', 'payme' => 'Payme', 'humo' => 'Humo', 'uzcard' => 'Uzcard', 'debt' => 'Qarz'];
        return view('admin.merchant.orders.show', compact('order', 'paymentLabels'));
    }

    public function returnOrder(Request $request, Order $order)
    {
        if ($order->organization_id !== $this->orgId()) abort(403);
        if ($order->status !== 'completed') return back()->with('error', 'Bu chekni qaytarib bo\'lmaydi.');

        DB::beginTransaction();
        try {
            foreach ($order->items as $item) {
                if ($item->product && $item->product->track_stock) {
                    Stock::where('product_id', $item->product_id)->where('branch_id', $order->branch_id)
                        ->increment('quantity', $item->quantity);
                }
            }
            $order->update(['status' => 'returned']);
            DB::commit();
            return back()->with('success', 'Chek qaytarildi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }
}
