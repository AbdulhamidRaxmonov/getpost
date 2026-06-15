@extends('admin.layouts.app')

@section('title', 'Yangi mahsulot')
@section('breadcrumb', 'Mahsulotlar / Yangi')

@section('content')
<div class="max-w-3xl">
    <div class="mb-5 flex items-center gap-3">
        <a href="{{ route('merchant.products.index') }}" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Yangi mahsulot qo'shish</h1>
    </div>

    <form action="{{ route('merchant.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-box text-blue-500"></i> Asosiy ma'lumotlar
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mahsulot nomi <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU (Artikul) <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku') }}" required placeholder="10001"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shtrix kod (Barcode)</label>
                    <div class="relative">
                        <input type="text" name="barcode" value="{{ old('barcode') }}" placeholder="4680001234567"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono pr-10">
                        <i class="fas fa-barcode absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategoriya</label>
                    <select name="category_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tanlang...</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">O'lchov birligi</label>
                    <select name="unit_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tanlang...</option>
                        @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->short_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rasm</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tavsif</label>
                <textarea name="description" rows="2" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- Prices -->
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-tag text-green-500"></i> Narxlar
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sotib olish narxi (so'm)</label>
                    <input type="number" name="purchase_price" value="{{ old('purchase_price', 0) }}" min="0" step="1"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sotish narxi (so'm) <span class="text-red-500">*</span></label>
                    <input type="number" name="selling_price" value="{{ old('selling_price') }}" min="0" step="1" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimal narx (so'm)</label>
                    <input type="number" name="min_price" value="{{ old('min_price', 0) }}" min="0" step="1"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">QQS %</label>
                    <input type="number" name="vat_percent" value="{{ old('vat_percent', 0) }}" min="0" max="100" step="0.1"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-cog text-gray-500"></i> Sozlamalar
            </h3>
            <div class="flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-600">
                    <span class="text-sm font-medium text-gray-700">Faol (savdoda ko'rsatilsin)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="track_stock" value="1" {{ old('track_stock', true) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-600">
                    <span class="text-sm font-medium text-gray-700">Ombor hisobi</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
                <i class="fas fa-save mr-2"></i> Saqlash
            </button>
            <a href="{{ route('merchant.products.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-medium transition">
                Bekor qilish
            </a>
        </div>
    </form>
</div>
@endsection
