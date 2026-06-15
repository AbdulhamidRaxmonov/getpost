@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Boshqaruv paneli')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-gray-500 text-sm mt-1">{{ now()->format('d.m.Y') }} - Bugungi holat</p>
        </div>
        <div class="flex gap-2">
            <span class="bg-white border border-gray-200 text-gray-600 px-3 py-1.5 rounded-lg text-sm shadow-sm">
                <i class="fas fa-clock text-blue-500 mr-1"></i>
                <span id="clock">{{ now()->format('H:i:s') }}</span>
            </span>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Bugungi savdolar</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['today_orders'] }}</p>
            <p class="text-xs text-gray-400 mt-1">ta chek</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Bugungi tushum</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($stats['today_sales'], 0, '.', ' ') }}</p>
            <p class="text-xs text-gray-400 mt-1">so'm</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Oylik tushum</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($stats['month_sales'], 0, '.', ' ') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ now()->format('F') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Mahsulotlar</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_products'] }}</p>
            @if($stats['low_stock'] > 0)
            <p class="text-xs text-red-500 mt-1 font-medium">
                <i class="fas fa-exclamation-triangle mr-1"></i>{{ $stats['low_stock'] }} ta kam
            </p>
            @else
            <p class="text-xs text-green-500 mt-1">Ombor yaxshi</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Oxirgi 7 kunlik sotuv</h3>
                <a href="{{ route('merchant.reports.sales') }}" class="text-xs text-blue-500 hover:underline">Batafsil</a>
            </div>
            <canvas id="salesChart" height="100"></canvas>
        </div>

        <!-- Today by payment -->
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Bugun to'lov usuli</h3>
            </div>
            @php
                $payLabels = ['cash' => ['Naqd', 'green', 'fa-money-bill'], 'card' => ['Plastik', 'blue', 'fa-credit-card'],
                    'click' => ['Click', 'indigo', 'fa-mobile'], 'payme' => ['Payme', 'blue', 'fa-mobile-alt'],
                    'humo' => ['Humo', 'orange', 'fa-credit-card'], 'uzcard' => ['Uzcard', 'purple', 'fa-credit-card'],
                    'debt' => ['Qarz', 'red', 'fa-handshake']];
            @endphp
            <div class="space-y-3">
                @foreach($payLabels as $key => $info)
                @if(isset($byPayment[$key]))
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-{{ $info[1] }}-100 rounded-lg flex items-center justify-center">
                            <i class="fas {{ $info[2] }} text-{{ $info[1] }}-600 text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-700">{{ $info[0] }}</span>
                    </div>
                    <span class="text-sm font-semibold text-gray-800">{{ number_format($byPayment[$key]->total, 0, '.', ' ') }}</span>
                </div>
                @endif
                @endforeach
                @if($byPayment->isEmpty())
                <p class="text-center text-gray-400 text-sm py-4">Bugun savdo yo'q</p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Oxirgi savdolar</h3>
                <a href="{{ route('merchant.orders.index') }}" class="text-xs text-blue-500 hover:underline">Barchasi</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentOrders as $order)
                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $order->receipt_number }}</p>
                        <p class="text-xs text-gray-400">{{ $order->user?->name }} • {{ $order->created_at->format('H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-800">{{ number_format($order->total_amount, 0, '.', ' ') }}</p>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ match($order->status) { 'completed' => 'bg-green-100 text-green-700', 'returned' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-600' } }}">
                            {{ match($order->status) { 'completed' => 'Bajarildi', 'returned' => 'Qaytarildi', default => $order->status } }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Savdolar yo'q</div>
                @endforelse
            </div>
        </div>

        <!-- Top products today -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Bugungi top mahsulotlar</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($topProducts as $idx => $product)
                <div class="px-5 py-3 flex items-center gap-3 hover:bg-gray-50 transition">
                    <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 text-xs font-bold flex items-center justify-center flex-shrink-0">
                        {{ $idx + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $product->product_name }}</p>
                        <p class="text-xs text-gray-400">{{ number_format($product->qty, 2) }} dona</p>
                    </div>
                    <p class="text-sm font-semibold text-gray-700">{{ number_format($product->total, 0, '.', ' ') }}</p>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Bugun savdo yo'q</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    @if($stats['low_stock'] > 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
            <div>
                <p class="font-medium text-yellow-800">Omborda {{ $stats['low_stock'] }} ta mahsulot kam!</p>
                <p class="text-yellow-600 text-sm">Qo'shimcha mahsulot kiritish kerak</p>
            </div>
        </div>
        <a href="{{ route('merchant.stocks.index') }}?low_stock=1" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            Ko'rish
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    setInterval(() => {
        document.getElementById('clock').textContent = new Date().toLocaleTimeString('uz-UZ');
    }, 1000);

    const chartData = @json($salesChart);
    new Chart(document.getElementById('salesChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [{
                label: "Sotuv (so'm)",
                data: chartData.map(d => d.total),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2,
                pointBackgroundColor: '#3b82f6',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => v.toLocaleString() + " so'm" },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush
