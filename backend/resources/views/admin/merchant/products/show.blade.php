@extends('admin.layouts.app')
@section('title', $product->name)
@section('content')
<div class="max-w-3xl space-y-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('merchant.products.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
            <h1 class="text-2xl font-bold text-gray-800">{{ $product->name }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('merchant.products.edit', $product) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-edit mr-1"></i> Tahrirlash
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            @if($product->image)
            <img src="{{ asset('storage/'.$product->image) }}" class="w-full max-h-48 object-contain rounded-lg bg-gray-50 mb-4">
            @endif
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-50"><dt class="text-gray-500">SKU (Artikul)</dt><dd class="font-mono font-medium">{{ $product->sku }}</dd></div>
                <div class="flex justify-between py-2 border-b border-gray-50"><dt class="text-gray-500">Barcode</dt><dd class="font-mono">{{ $product->barcode ?? '—' }}</dd></div>
                <div class="flex justify-between py-2 border-b border-gray-50"><dt class="text-gray-500">Kategoriya</dt>
                    <dd>@if($product->category)<span class="text-xs px-2 py-0.5 rounded-full text-white" style="background: {{ $product->category->color }}">{{ $product->category->name }}</span>@else—@endif</dd></div>
                <div class="flex justify-between py-2 border-b border-gray-50"><dt class="text-gray-500">O'lchov</dt><dd>{{ $product->unit?->name ?? '—' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Holat</dt><dd><span class="text-xs px-2 py-0.5 rounded-full {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $product->is_active ? 'Faol' : 'Nofaol' }}</span></dd></div>
            </dl>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800 mb-3">Narxlar</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-50"><dt class="text-gray-500">Sotib olish narxi</dt><dd class="font-medium">{{ number_format($product->purchase_price, 0, '.', ' ') }} so'm</dd></div>
                <div class="flex justify-between py-2 border-b border-gray-50"><dt class="text-gray-500">Sotish narxi</dt><dd class="font-bold text-blue-600 text-lg">{{ number_format($product->selling_price, 0, '.', ' ') }} so'm</dd></div>
                <div class="flex justify-between py-2 border-b border-gray-50"><dt class="text-gray-500">Minimal narx</dt><dd class="font-medium">{{ number_format($product->min_price, 0, '.', ' ') }} so'm</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">QQS</dt><dd>{{ $product->vat_percent }}%</dd></div>
            </dl>

            <h3 class="font-semibold text-gray-800 mt-5 mb-3">Ombor qoldiqlari</h3>
            @forelse($product->stocks as $stock)
            <div class="flex justify-between items-center py-2 border-b border-gray-50 text-sm">
                <span class="text-gray-600">{{ $stock->branch->name ?? '—' }}</span>
                <span class="font-bold text-lg {{ $stock->quantity <= $stock->min_quantity ? 'text-red-600' : 'text-gray-800' }}">
                    {{ number_format($stock->quantity, 2) }}
                </span>
            </div>
            @empty
            <p class="text-gray-400 text-sm">Ombor ma'lumoti yo'q</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
