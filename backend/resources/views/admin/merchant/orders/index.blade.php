@extends('admin.layouts.app')

@section('title', 'Savdolar')
@section('breadcrumb', 'Savdolar')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Savdolar</h1>
            <p class="text-gray-500 text-sm">Jami {{ $orders->total() }} ta yozuv</p>
        </div>
        <a href="{{ route('merchant.reports.sales') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <i class="fas fa-chart-bar mr-1"></i> Hisobot
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-3">
        <div class="flex-1 min-w-48">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Chek raqami..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Barcha holat</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Bajarildi</option>
            <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Qaytarildi</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Bekor qilindi</option>
        </select>
        <select name="payment_method" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Barcha to'lov</option>
            @foreach($paymentLabels as $key => $label)
            <option value="{{ $key }}" {{ request('payment_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
            <i class="fas fa-filter mr-1"></i> Filter
        </button>
        @if(request()->hasAny(['search', 'status', 'payment_method', 'date_from', 'date_to']))
        <a href="{{ route('merchant.orders.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
            <i class="fas fa-times"></i>
        </a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Chek №</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Kassir</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Mijoz</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">To'lov</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Summa</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Holat</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Sana</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4">
                        <span class="font-mono text-sm font-medium text-gray-800">{{ $order->receipt_number }}</span>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $order->user?->name ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $order->customer?->name ?? 'Noma\'lum' }}</td>
                    <td class="px-5 py-4 text-center">
                        <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 font-medium">
                            {{ $paymentLabels[$order->payment_method] ?? $order->payment_method }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <span class="font-semibold text-gray-800 text-sm">{{ number_format($order->total_amount, 0, '.', ' ') }}</span>
                        <span class="text-xs text-gray-400 ml-1">so'm</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        @php
                            $statusMap = ['completed' => ['bg-green-100 text-green-700', 'Bajarildi'], 'returned' => ['bg-red-100 text-red-700', 'Qaytarildi'], 'cancelled' => ['bg-gray-100 text-gray-600', 'Bekor'], 'draft' => ['bg-yellow-100 text-yellow-700', 'Qoralama']];
                            $sc = $statusMap[$order->status] ?? ['bg-gray-100 text-gray-600', $order->status];
                        @endphp
                        <span class="text-xs px-2 py-1 rounded-full font-medium {{ $sc[0] }}">{{ $sc[1] }}</span>
                    </td>
                    <td class="px-5 py-4 text-center text-sm text-gray-500">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('merchant.orders.show', $order) }}"
                               class="text-blue-500 hover:text-blue-700 p-1.5 hover:bg-blue-50 rounded transition">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($order->status === 'completed')
                            <form action="{{ route('merchant.orders.return', $order) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Chekni qaytarishni tasdiqlaysizmi?')">
                                @csrf
                                <button type="submit" class="text-red-500 hover:text-red-700 p-1.5 hover:bg-red-50 rounded transition" title="Qaytarish">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                        <i class="fas fa-receipt text-5xl mb-3 block"></i>
                        <p>Savdolar topilmadi</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($orders->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $orders->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
