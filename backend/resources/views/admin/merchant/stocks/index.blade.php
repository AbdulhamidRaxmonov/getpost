@extends('admin.layouts.app')
@section('title', 'Ombor holati')
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Ombor holati</h1>
    </div>
    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Mahsulot nomi..."
               class="flex-1 min-w-48 px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="branch_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Barcha filiallar</option>
            @foreach($branches as $branch)
            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer">
            <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}>
            <span class="text-sm text-gray-700">Faqat kam qolganlar</span>
        </label>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Filter</button>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">SKU</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Mahsulot</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Kategoriya</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Qoldiq</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Min qoldiq</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Holat</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Sozlash</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($products as $product)
                @php $stock = $product->stocks->first(); $qty = $stock?->quantity ?? 0; $minQty = $stock?->min_quantity ?? 0; @endphp
                <tr class="hover:bg-gray-50 {{ $qty <= $minQty ? 'bg-red-50' : '' }}">
                    <td class="px-5 py-4 text-sm font-mono text-gray-700">{{ $product->sku }}</td>
                    <td class="px-5 py-4 text-sm font-medium text-gray-800">{{ $product->name }}</td>
                    <td class="px-5 py-4">
                        @if($product->category)
                        <span class="text-xs px-2 py-0.5 rounded-full text-white" style="background: {{ $product->category->color }}">{{ $product->category->name }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-bold text-lg {{ $qty <= $minQty ? 'text-red-600' : 'text-gray-800' }}">{{ number_format($qty, 2) }}</span>
                        <span class="text-xs text-gray-400 ml-1">{{ $product->unit?->short_name }}</span>
                    </td>
                    <td class="px-5 py-4 text-center text-sm text-gray-500">{{ number_format($minQty, 2) }}</td>
                    <td class="px-5 py-4 text-center">
                        @if($qty <= $minQty)
                        <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Kam
                        </span>
                        @else
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Yaxshi</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        <button onclick="document.getElementById('adj-{{ $product->id }}').classList.toggle('hidden')"
                                class="text-blue-500 hover:text-blue-700 p-1.5 hover:bg-blue-50 rounded transition text-sm">
                            <i class="fas fa-edit"></i>
                        </button>
                        <div id="adj-{{ $product->id }}" class="hidden mt-2">
                            <form action="{{ route('merchant.stocks.adjust') }}" method="POST" class="flex gap-1 items-center justify-center">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="branch_id" value="{{ $stock?->branch_id ?? request('branch_id', 1) }}">
                                <select name="type" class="text-xs border border-gray-200 rounded px-1 py-1">
                                    <option value="set">Belgilash</option>
                                    <option value="add">Qo'shish</option>
                                    <option value="subtract">Ayirish</option>
                                </select>
                                <input type="number" name="quantity" step="0.001" min="0" placeholder="0" class="w-16 text-xs border border-gray-200 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <button type="submit" class="bg-blue-500 text-white text-xs px-2 py-1 rounded hover:bg-blue-600">OK</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">Mahsulotlar topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($products->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $products->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
