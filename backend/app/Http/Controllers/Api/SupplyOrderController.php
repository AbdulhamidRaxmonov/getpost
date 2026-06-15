<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupplyOrder;
use App\Models\SupplyOrderItem;
use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplyOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplyOrder::with(['supplier', 'user'])
            ->where('organization_id', $request->user()->organization_id);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('supply_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('supply_date', '<=', $request->date_to);
        }

        return response()->json($query->orderBy('supply_date', 'desc')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.selling_price' => 'required|numeric|min:0',
            'supply_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('organization_id', $request->user()->organization_id)
                    ->firstOrFail();

                $lineTotal = $item['quantity'] * $item['purchase_price'];
                $totalAmount += $lineTotal;

                $itemsData[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'selling_price' => $item['selling_price'],
                    'total_amount' => $lineTotal,
                ];
            }

            $supplyOrder = SupplyOrder::create([
                'organization_id' => $request->user()->organization_id,
                'branch_id' => $request->branch_id,
                'supplier_id' => $request->supplier_id,
                'user_id' => $request->user()->id,
                'invoice_number' => $request->invoice_number,
                'status' => 'confirmed',
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount ?? $totalAmount,
                'supply_date' => $request->supply_date,
                'note' => $request->note,
            ]);

            foreach ($itemsData as $itemData) {
                SupplyOrderItem::create([
                    'supply_order_id' => $supplyOrder->id,
                    'product_id' => $itemData['product']->id,
                    'quantity' => $itemData['quantity'],
                    'purchase_price' => $itemData['purchase_price'],
                    'selling_price' => $itemData['selling_price'],
                    'total_amount' => $itemData['total_amount'],
                ]);

                // Update product prices
                $itemData['product']->update([
                    'purchase_price' => $itemData['purchase_price'],
                    'selling_price' => $itemData['selling_price'],
                ]);

                // Add to stock
                Stock::updateOrCreate(
                    ['product_id' => $itemData['product']->id, 'branch_id' => $request->branch_id],
                    ['quantity' => DB::raw("quantity + {$itemData['quantity']}")]
                );
            }

            DB::commit();

            return response()->json($supplyOrder->load('items.product', 'supplier'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Xatolik: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request, SupplyOrder $supplyOrder)
    {
        if ($supplyOrder->organization_id !== $request->user()->organization_id) abort(403);
        return response()->json($supplyOrder->load('items.product', 'supplier', 'user'));
    }

    public function update(Request $request, SupplyOrder $supplyOrder)
    {
        if ($supplyOrder->organization_id !== $request->user()->organization_id) abort(403);
        $supplyOrder->update($request->only(['invoice_number', 'note', 'paid_amount']));
        return response()->json($supplyOrder);
    }

    public function destroy(Request $request, SupplyOrder $supplyOrder)
    {
        if ($supplyOrder->organization_id !== $request->user()->organization_id) abort(403);
        $supplyOrder->delete();
        return response()->json(['message' => 'Kirim o\'chirildi.']);
    }
}
