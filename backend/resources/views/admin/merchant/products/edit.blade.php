@extends('admin.layouts.app')
@section('title', 'Mahsulot tahrirlash')
@section('breadcrumb', 'Mahsulotlar / Tahrirlash')

@section('content')
<div class="max-w-3xl">
    <div class="mb-5 flex items-center gap-3">
        <a href="{{ route('merchant.products.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $product->name }} - Tahrirlash</h1>
    </div>

    <form action="{{ route('merchant.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i class="fas fa-box text-blue-500"></i> Asosiy ma'lumotlar</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Mahsulot nomi *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">SKU (Artikul)</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Shtrix kod</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Kategoriya</label>
                    <select name="category_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tanlang...</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">O'lchov birligi</label>
                    <select name="unit_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tanlang...</option>
                        @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->short_name }})</option>
                        @endforeach
                    </select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Rasm</label>
                    @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" class="w-16 h-16 rounded-lg object-cover mb-2">
                    @endif
                    <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i class="fas fa-tag text-green-500"></i> Narxlar</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Sotib olish narxi</label>
                    <input type="number" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" min="0" step="1" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Sotish narxi *</label>
                    <input type="number" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" min="0" step="1" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Minimal narx</label>
                    <input type="number" name="min_price" value="{{ old('min_price', $product->min_price) }}" min="0" step="1" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">QQS %</label>
                    <input type="number" name="vat_percent" value="{{ old('vat_percent', $product->vat_percent) }}" min="0" max="100" step="0.1" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-blue-600">
                    <span class="text-sm font-medium text-gray-700">Faol</span></label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="track_stock" value="1" {{ old('track_stock', $product->track_stock) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-blue-600">
                    <span class="text-sm font-medium text-gray-700">Ombor hisobi</span></label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition"><i class="fas fa-save mr-2"></i> Saqlash</button>
            <a href="{{ route('merchant.products.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-medium transition">Bekor qilish</a>
        </div>
    </form>
</div>
@endsection
