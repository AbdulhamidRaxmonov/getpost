@extends('admin.layouts.app')

@section('title', 'Tashkilotlar')
@section('breadcrumb', 'Super Admin / Tashkilotlar')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tashkilotlar</h1>
            <p class="text-gray-500 text-sm mt-1">Barcha tadbirkor tashkilotlarini boshqarish</p>
        </div>
        <a href="{{ route('super.organizations.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition font-medium">
            <i class="fas fa-plus"></i>
            Yangi tashkilot
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Tashkilot nomi yoki telefon..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
            </div>
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Barcha holat</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Faol</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Bloklangan</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
                <i class="fas fa-filter mr-1"></i> Filterlash
            </button>
            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('super.organizations.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                <i class="fas fa-times mr-1"></i> Tozalash
            </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">№</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tashkilot</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Telefon</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tarif</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Filial</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Foydalanuvchi</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Savdo</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Holat</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($organizations as $org)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4 text-gray-500 text-sm">{{ $organizations->firstItem() + $loop->index }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-building text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ $org->name }}</p>
                                @if($org->legal_name)
                                <p class="text-xs text-gray-400">{{ $org->legal_name }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $org->phone }}</td>
                    <td class="px-5 py-4">
                        @php
                            $planColors = ['basic' => 'gray', 'pro' => 'blue', 'enterprise' => 'purple'];
                            $color = $planColors[$org->subscription_plan] ?? 'gray';
                        @endphp
                        <span class="text-xs px-2 py-1 rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 font-medium capitalize">
                            {{ $org->subscription_plan }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center text-sm font-medium text-gray-700">{{ $org->branches_count }}</td>
                    <td class="px-5 py-4 text-center text-sm font-medium text-gray-700">{{ $org->users_count }}</td>
                    <td class="px-5 py-4 text-center text-sm font-medium text-gray-700">{{ number_format($org->orders_count) }}</td>
                    <td class="px-5 py-4 text-center">
                        <span class="text-xs px-2 py-1 rounded-full font-medium {{ $org->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            <i class="fas fa-circle text-xs mr-1"></i>
                            {{ $org->is_active ? 'Faol' : 'Bloklangan' }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('super.organizations.show', $org) }}"
                               class="text-blue-600 hover:text-blue-800 p-1.5 hover:bg-blue-50 rounded transition" title="Ko'rish">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('super.organizations.edit', $org) }}"
                               class="text-yellow-600 hover:text-yellow-800 p-1.5 hover:bg-yellow-50 rounded transition" title="Tahrirlash">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('super.organizations.toggle-status', $org) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="{{ $org->is_active ? 'text-red-500 hover:text-red-700 hover:bg-red-50' : 'text-green-500 hover:text-green-700 hover:bg-green-50' }} p-1.5 rounded transition"
                                        title="{{ $org->is_active ? 'Bloklash' : 'Faollashtirish' }}">
                                    <i class="fas {{ $org->is_active ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('super.organizations.destroy', $org) }}" method="POST" class="inline"
                                  onsubmit="return confirm('{{ $org->name }} tashkilotini o\'chirishni tasdiqlaysizmi?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-red-500 hover:text-red-700 p-1.5 hover:bg-red-50 rounded transition" title="O'chirish">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-5 py-12 text-center text-gray-400">
                        <i class="fas fa-building text-5xl mb-3 block"></i>
                        <p>Tashkilotlar topilmadi</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($organizations->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $organizations->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
