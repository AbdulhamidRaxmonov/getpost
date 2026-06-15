@extends('admin.layouts.app')
@section('title', 'Kategoriyalar')
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Kategoriyalar</h1>
        <a href="{{ route('merchant.categories.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
            <i class="fas fa-plus mr-1"></i> Yangi kategoriya
        </a>
    </div>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Rang</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Nomi</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Mahsulotlar</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Holat</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($categories as $cat)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-4"><div class="w-8 h-8 rounded-lg" style="background: {{ $cat->color }}"></div></td>
                    <td class="px-5 py-4 text-sm font-medium text-gray-800">{{ $cat->name }}</td>
                    <td class="px-5 py-4 text-center text-sm text-gray-600">{{ $cat->products_count }}</td>
                    <td class="px-5 py-4 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $cat->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $cat->is_active ? 'Faol' : 'Nofaol' }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <a href="{{ route('merchant.categories.edit', $cat) }}" class="text-yellow-500 hover:text-yellow-700 p-1.5 hover:bg-yellow-50 rounded transition"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('merchant.categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('O\'chirilsinmi?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 p-1.5 hover:bg-red-50 rounded transition"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">Kategoriyalar topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
