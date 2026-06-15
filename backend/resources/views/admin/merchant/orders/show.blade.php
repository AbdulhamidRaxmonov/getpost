@extends('admin.layouts.app')

@section('title', 'Chek ' . $order->receipt_number)
@section('breadcrumb', 'Savdolar / ' . $order->receipt_number)

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('merchant.orders.index') }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $order->receipt_number }}</h1>
                <p class="text-gray-500 text-sm">{{ $order->created_at->format('d.m.Y H:i:s') }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            @if($order->status === 'completed')
            <form action="{{ route('merchant.orders.return', $order) }}" method="POST" class="inline"
                  onsubmit="return confirm('Chekni qaytarishni tasdiqlaysizmi?')">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-undo mr-1"></i> Qaytarish
                </button>
            </form>
            @endif
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-print mr-1"></i> Chop etish
            </button>
        </div>
    </div>

    <!-- Receipt Card -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Header info -->
        <div class="p-6 bg-gray-50 border-b border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500 uppercase mb-1">Kassir</p>
                <p class="font-medium text-gray-800 text-sm">{{ $order->user?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase mb-1">Terminal</p>
                <p class="font-medium text-gray-800 text-sm">{{ $order->terminal?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase mb-1">Filial</p>
                <p class="font-medium text-gray-800 text-sm">{{ $order->branch?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase mb-1">Holat</p>
                @php $sc = match($order->status) { 'completed' => 'bg-green-100 text-green-700', 'returned' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-700' }; @endphp
                <span class="text-xs px-2 py-1 rounded-full font-medium {{ $sc }}">
                    {{ match($order->status) { 'completed' => 'Bajarildi', 'returned' => 'Qaytarildi', default => $order->status } }}
                </span>
            </div>
        </div>

        <!-- Items -->
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">№</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Mahsulot</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Narx</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Miqdor</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Chegirma</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Summa</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($order->items as $idx => $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $idx + 1 }}</td>
                    <td class="px-5 py-3">
                        <p class="text-sm font-medium text-gray-800">{{ $item->product_name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $item->product_sku }}</p>
                    </td>
                    <td class="px-5 py-3 text-right text-sm text-gray-600">{{ number_format($item->unit_price, 0, '.', ' ') }}</td>
                    <td class="px-5 py-3 text-right text-sm text-gray-600">{{ $item->quantity }}</td>
                    <td class="px-5 py-3 text-right text-sm text-gray-500">{{ $item->discount_percent }}%</td>
                    <td class="px-5 py-3 text-right text-sm font-semibold text-gray-800">{{ number_format($item->total_amount, 0, '.', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="p-6 border-t border-gray-100">
            <div class="max-w-xs ml-auto space-y-2">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Jami:</span>
                    <span>{{ number_format($order->subtotal, 0, '.', ' ') }} so'm</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-sm text-red-600">
                    <span>Chegirma:</span>
                    <span>-{{ number_format($order->discount_amount, 0, '.', ' ') }} so'm</span>
                </div>
                @endif
                @if($order->vat_amount > 0)
                <div class="flex justify-between text-sm text-gray-600">
                    <span>QQS:</span>
                    <span>{{ number_format($order->vat_amount, 0, '.', ' ') }} so'm</span>
                </div>
                @endif
                <div class="flex justify-between font-bold text-lg text-gray-800 border-t border-gray-200 pt-2 mt-2">
                    <span>Umumiy:</span>
                    <span>{{ number_format($order->total_amount, 0, '.', ' ') }} so'm</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                    <span>To'langan:</span>
                    <span class="text-blue-600 font-medium">{{ number_format($order->paid_amount, 0, '.', ' ') }} so'm</span>
                </div>
                @if($order->change_amount > 0)
                <div class="flex justify-between text-sm text-green-600">
                    <span>Qaytim:</span>
                    <span>{{ number_format($order->change_amount, 0, '.', ' ') }} so'm</span>
                </div>
                @endif
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
                <div class="text-gray-600">
                    <i class="fas fa-credit-card mr-1"></i>
                    To'lov usuli: <strong>{{ $paymentLabels[$order->payment_method] ?? $order->payment_method }}</strong>
                </div>
                @if($order->customer)
                <div class="text-gray-600">
                    <i class="fas fa-user mr-1"></i>
                    Mijoz: <strong>{{ $order->customer->name }}</strong>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
