<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $branchId = $request->query('branch_id', $request->user()->branch_id);

        $query = Product::with(['category', 'unit'])
            ->where('organization_id', $orgId)
            ->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->get();

        // Add stock info for each product
        if ($branchId) {
            $stocks = Stock::where('branch_id', $branchId)
                ->whereIn('product_id', $products->pluck('id'))
                ->get()
                ->keyBy('product_id');

            $products->each(function ($product) use ($stocks) {
                $stock = $stocks->get($product->id);
                $product->stock_quantity = $stock ? $stock->quantity : 0;
            });
        }

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50',
            'selling_price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        $orgId = $request->user()->organization_id;

        // Check SKU uniqueness in organization
        $exists = Product::where('organization_id', $orgId)
            ->where('sku', $request->sku)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Bu SKU (artikul) allaqachon mavjud.'], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'organization_id' => $orgId,
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

        return response()->json($product->load('category', 'unit'), 201);
    }

    public function show(Request $request, Product $product)
    {
        $this->checkOrganization($request, $product);

        return response()->json($product->load('category', 'unit', 'stocks.branch'));
    }

    public function update(Request $request, Product $product)
    {
        $this->checkOrganization($request, $product);

        $request->validate([
            'name' => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'sku' => $request->sku ?? $product->sku,
            'barcode' => $request->barcode,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
            'purchase_price' => $request->purchase_price ?? $product->purchase_price,
            'selling_price' => $request->selling_price,
            'min_price' => $request->min_price ?? $product->min_price,
            'vat_percent' => $request->vat_percent ?? $product->vat_percent,
            'is_active' => $request->boolean('is_active', $product->is_active),
            'track_stock' => $request->boolean('track_stock', $product->track_stock),
        ]);

        return response()->json($product->load('category', 'unit'));
    }

    public function destroy(Request $request, Product $product)
    {
        $this->checkOrganization($request, $product);
        $product->delete();
        return response()->json(['message' => 'Mahsulot o\'chirildi.']);
    }

    public function searchByBarcode(Request $request)
    {
        $request->validate(['barcode' => 'required|string']);

        $product = Product::with(['category', 'unit'])
            ->where('organization_id', $request->user()->organization_id)
            ->where('barcode', $request->barcode)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Mahsulot topilmadi.'], 404);
        }

        return response()->json($product);
    }

    private function checkOrganization(Request $request, Product $product): void
    {
        if ($product->organization_id !== $request->user()->organization_id) {
            abort(403, 'Ruxsat yo\'q.');
        }
    }
}
