<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::with(['product.category', 'product.unit', 'branch'])
            ->whereHas('product', function ($q) use ($request) {
                $q->where('organization_id', $request->user()->organization_id);
            });

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('low_stock')) {
            $query->whereColumn('quantity', '<=', 'min_quantity');
        }

        return response()->json($query->get());
    }

    public function adjust(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'quantity' => 'required|numeric',
            'type' => 'required|in:set,add,subtract',
            'note' => 'nullable|string',
        ]);

        $product = Product::where('id', $request->product_id)
            ->where('organization_id', $request->user()->organization_id)
            ->firstOrFail();

        $stock = Stock::firstOrCreate(
            ['product_id' => $product->id, 'branch_id' => $request->branch_id],
            ['quantity' => 0, 'min_quantity' => 0]
        );

        switch ($request->type) {
            case 'set':
                $stock->update(['quantity' => $request->quantity]);
                break;
            case 'add':
                $stock->increment('quantity', $request->quantity);
                break;
            case 'subtract':
                $stock->decrement('quantity', $request->quantity);
                break;
        }

        return response()->json(['message' => 'Ombor yangilandi.', 'stock' => $stock->fresh()]);
    }
}
