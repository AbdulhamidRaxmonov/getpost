<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Models\SupplyOrder;
use App\Models\SupplyOrderItem;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplyOrderController extends Controller
{
    private function orgId() { return auth()->user()->organization_id; }

    public function index(Request $request)
    {
        $orders = SupplyOrder::where('organization_id', $this->orgId())
            ->with(['supplier', 'user', 'branch'])
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('supply_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('supply_date', '<=', $request->date_to))
            ->latest('supply_date')
            ->paginate(20);
        return view('admin.merchant.supply-orders.index', compact('orders'));
    }

    public function create()
    {
        $products = Product::where('organization_id', $this->orgId())->where('is_active', true)->with('unit')->orderBy('name')->get();
        $branches = Branch::where('organization_id', $this->orgId())->where('is_active', true)->get();
        $suppliers = Supplier::where('organization_id', $this->orgId())->get();
        return view('admin.merchant.supply-orders.create', compact('products', 'branches', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'supply_date' => 'required|date',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                if (empty($item['product_id']) || empty($item['quantity'])) continue;
                $product = Product::findOrFail($item['product_id']);
                $lineTotal = $item['quantity'] * $item['purchase_price'];
                $totalAmount += $lineTotal;
                $itemsData[] = array_merge($item, ['product' => $product, 'total_amount' => $lineTotal]);
            }

            $so = SupplyOrder::create([
                'organization_id' => $this->orgId(),
                'branch_id' => $request->branch_id,
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'invoice_number' => $request->invoice_number,
                'status' => 'confirmed',
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount ?? $totalAmount,
                'supply_date' => $request->supply_date,
                'note' => $request->note,
            ]);

            foreach ($itemsData as $d) {
                SupplyOrderItem::create([
                    'supply_order_id' => $so->id,
                    'product_id' => $d['product_id'],
                    'quantity' => $d['quantity'],
                    'purchase_price' => $d['purchase_price'],
                    'selling_price' => $d['selling_price'],
                    'total_amount' => $d['total_amount'],
                ]);
                $d['product']->update(['purchase_price' => $d['purchase_price'], 'selling_price' => $d['selling_price']]);
                Stock::updateOrCreate(
                    ['product_id' => $d['product_id'], 'branch_id' => $request->branch_id],
                    ['quantity' => DB::raw("quantity + {$d['quantity']}")]
                );
            }

            DB::commit();
            return redirect()->route('merchant.supply-orders.index')->with('success', 'Kirim saqlandi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    public function show(SupplyOrder $supplyOrder)
    {
        if ($supplyOrder->organization_id !== $this->orgId()) abort(403);
        $supplyOrder->load(['items.product.unit', 'supplier', 'user', 'branch']);
        return view('admin.merchant.supply-orders.show', compact('supplyOrder'));
    }

    public function edit(SupplyOrder $supplyOrder) { abort(404); }
    public function update(Request $request, SupplyOrder $supplyOrder) { abort(404); }

    public function destroy(SupplyOrder $supplyOrder)
    {
        if ($supplyOrder->organization_id !== $this->orgId()) abort(403);
        $supplyOrder->delete();
        return redirect()->route('merchant.supply-orders.index')->with('success', 'Kirim o\'chirildi.');
    }
}
