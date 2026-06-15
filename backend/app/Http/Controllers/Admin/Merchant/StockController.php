<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;

class StockController extends Controller
{
    private function orgId() { return auth()->user()->organization_id; }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit', 'stocks' => function ($q) use ($request) {
            if ($request->filled('branch_id')) $q->where('branch_id', $request->branch_id);
        }])->where('organization_id', $this->orgId())->where('track_stock', true);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($sq) => $sq->where('name', 'like', "%$q%")->orWhere('sku', 'like', "%$q%"));
        }
        if ($request->boolean('low_stock')) {
            $query->whereHas('stocks', fn($sq) => $sq->whereColumn('quantity', '<=', 'min_quantity'));
        }

        $products = $query->orderBy('name')->paginate(30);
        $branches = Branch::where('organization_id', $this->orgId())->get();

        return view('admin.merchant.stocks.index', compact('products', 'branches'));
    }

    public function adjust(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'quantity' => 'required|numeric',
            'type' => 'required|in:set,add,subtract',
        ]);

        $product = Product::where('id', $request->product_id)->where('organization_id', $this->orgId())->firstOrFail();
        $stock = Stock::firstOrCreate(['product_id' => $product->id, 'branch_id' => $request->branch_id], ['quantity' => 0]);

        match ($request->type) {
            'set' => $stock->update(['quantity' => $request->quantity]),
            'add' => $stock->increment('quantity', $request->quantity),
            'subtract' => $stock->decrement('quantity', $request->quantity),
        };

        return back()->with('success', 'Ombor yangilandi!');
    }
}
