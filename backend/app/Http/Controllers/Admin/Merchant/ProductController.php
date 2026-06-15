<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Branch;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private function orgId() { return auth()->user()->organization_id; }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit'])
            ->where('organization_id', $this->orgId());

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%$q%")
                   ->orWhere('sku', 'like', "%$q%")
                   ->orWhere('barcode', 'like', "%$q%");
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->orderBy('name')->paginate(20);
        $categories = Category::where('organization_id', $this->orgId())->orderBy('name')->get();

        return view('admin.merchant.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('organization_id', $this->orgId())->orderBy('name')->get();
        $units = Unit::where('organization_id', $this->orgId())->orderBy('name')->get();
        return view('admin.merchant.products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $exists = Product::where('organization_id', $this->orgId())->where('sku', $request->sku)->exists();
        if ($exists) return back()->withInput()->with('error', 'Bu SKU allaqachon mavjud.');

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'organization_id' => $this->orgId(),
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'sku' => $request->sku,
            'barcode' => $request->barcode,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
            'purchase_price' => $request->purchase_price ?? 0,
            'selling_price' => $request->selling_price,
            'min_price' => $request->min_price ?? 0,
            'vat_percent' => $request->vat_percent ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'track_stock' => $request->boolean('track_stock', true),
        ]);

        return redirect()->route('merchant.products.index')->with('success', 'Mahsulot qo\'shildi!');
    }

    public function show(Product $product)
    {
        if ($product->organization_id !== $this->orgId()) abort(403);
        $product->load(['category', 'unit', 'stocks.branch']);
        return view('admin.merchant.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        if ($product->organization_id !== $this->orgId()) abort(403);
        $categories = Category::where('organization_id', $this->orgId())->orderBy('name')->get();
        $units = Unit::where('organization_id', $this->orgId())->orderBy('name')->get();
        return view('admin.merchant.products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        if ($product->organization_id !== $this->orgId()) abort(403);
        $request->validate([
            'name' => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update(array_merge(
            $request->only(['category_id', 'unit_id', 'barcode', 'name', 'description', 'purchase_price', 'selling_price', 'min_price', 'vat_percent']),
            ['image' => $imagePath, 'is_active' => $request->boolean('is_active', $product->is_active), 'track_stock' => $request->boolean('track_stock', $product->track_stock)]
        ));

        return redirect()->route('merchant.products.index')->with('success', 'Mahsulot yangilandi!');
    }

    public function destroy(Product $product)
    {
        if ($product->organization_id !== $this->orgId()) abort(403);
        $product->delete();
        return redirect()->route('merchant.products.index')->with('success', 'Mahsulot o\'chirildi.');
    }

    public function toggle(Product $product)
    {
        if ($product->organization_id !== $this->orgId()) abort(403);
        $product->update(['is_active' => !$product->is_active]);
        return back()->with('success', 'Mahsulot holati o\'zgartirildi.');
    }
}
