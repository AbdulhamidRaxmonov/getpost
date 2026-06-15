@extends('admin.layouts.app')

@section('title', 'Mahsulotlar')
@section('breadcrumb', 'Mahsulotlar')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Mahsulotlar</h1>
            <p class="text-gray-500 text-sm">Jami {{ $products->total() }} ta mahsulot</p>
        </div>
        <a href="{{ route('merchant.products.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition font-medium">
            <i class="fas fa-plus"></i> Yangi mahsulot
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-3">
        <div class="flex-1 min-w-48">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, SKU yoki shtrix kod..."
                       class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <select name="category_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Barcha kategoriyalar</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Barcha holat</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Faol</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nofaol</option>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
            <i class="fas fa-search mr-1"></i> Qidirish
        </button>
        @if(request()->hasAny(['search', 'category_id', 'status']))
        <a href="{{ route('merchant.products.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
            <i class="fas fa-times mr-1"></i> Tozalash
        </a>
        @endif
    </form>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">SKU / Barcode</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Mahsulot nomi</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Kategoriya</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Sotib olish</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Sotish narxi</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Holat</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4">
                        <p class="text-sm font-mono font-medium text-gray-800">{{ $product->sku }}</p>
                        @if($product->barcode)
                        <p class="text-xs text-gray-400 font-mono">{{ $product->barcode }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                            @else
                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-box text-gray-400"></i>
                            </div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ $product->name }}</p>
                                @if($product->unit)
                                <p class="text-xs text-gray-400">{{ $product->unit->short_name }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @if($product->category)
                        <span class="text-xs px-2 py-1 rounded-full text-white font-medium" style="background: {{ $product->category->color }}">
                            {{ $product->category->name }}
                        </span>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right text-sm text-gray-600">{{ number_format($product->purchase_price, 0, '.', ' ') }}</td>
                    <td class="px-5 py-4 text-right">
                        <span class="text-sm font-semibold text-gray-800">{{ number_format($product->selling_price, 0, '.', ' ') }}</span>
                        <span class="text-xs text-gray-400 ml-1">so'm</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <form action="{{ route('merchant.products.toggle', $product) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs px-2 py-1 rounded-full font-medium transition {{ $product->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                <i class="fas fa-circle text-xs mr-1"></i>
                                {{ $product->is_active ? 'Faol' : 'Nofaol' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('merchant.products.show', $product) }}" class="text-blue-500 hover:text-blue-700 p-1.5 hover:bg-blue-50 rounded transition" title="Ko'rish">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('merchant.products.edit', $product) }}" class="text-yellow-500 hover:text-yellow-700 p-1.5 hover:bg-yellow-50 rounded transition" title="Tahrirlash">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('merchant.products.destroy', $product) }}" method="POST" class="inline"
                                  onsubmit="return confirm('{{ $product->name }} o\'chirilsinmi?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 p-1.5 hover:bg-red-50 rounded transition" title="O'chirish">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                        <i class="fas fa-box text-5xl mb-3 block"></i>
                        <p>Mahsulotlar topilmadi</p>
                        <a href="{{ route('merchant.products.create') }}" class="inline-block mt-3 text-blue-500 hover:underline text-sm">
                            + Yangi mahsulot qo'shish
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($products->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $products->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
