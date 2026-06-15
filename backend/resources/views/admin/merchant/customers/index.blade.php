@extends('admin.layouts.app')
@section('title', 'Mijozlar')
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Mijozlar</h1>
        <a href="{{ route('merchant.customers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium"><i class="fas fa-plus mr-1"></i> Yangi mijoz</a>
    </div>
    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ism yoki telefon..." class="flex-1 px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Qidirish</button>
    </form>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Ism</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Telefon</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Balans</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Chegirma</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Savdolar</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Amallar</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($customers as $c)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-4 text-sm font-medium text-gray-800">{{ $c->name }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $c->phone ?? '—' }}</td>
                    <td class="px-5 py-4 text-center text-sm {{ $c->balance < 0 ? 'text-red-600 font-medium' : 'text-gray-700' }}">{{ number_format($c->balance, 0, '.', ' ') }} so'm</td>
                    <td class="px-5 py-4 text-center text-sm text-gray-600">{{ $c->discount_percent }}%</td>
                    <td class="px-5 py-4 text-center text-sm text-gray-700">{{ $c->orders_count }}</td>
                    <td class="px-5 py-4 text-center">
                        <a href="{{ route('merchant.customers.show', $c) }}" class="text-blue-500 hover:text-blue-700 p-1.5 hover:bg-blue-50 rounded"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('merchant.customers.edit', $c) }}" class="text-yellow-500 hover:text-yellow-700 p-1.5 hover:bg-yellow-50 rounded"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('merchant.customers.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('O\'chirilsinmi?')">@csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 p-1.5 hover:bg-red-50 rounded"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Mijozlar topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($customers->hasPages())<div class="px-5 py-4 border-t">{{ $customers->withQueryString()->links() }}</div>@endif
    </div>
</div>
@endsection
