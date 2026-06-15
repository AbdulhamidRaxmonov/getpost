@extends('admin.layouts.app')

@section('title', 'Super Admin Dashboard')
@section('breadcrumb', 'Super Admin / Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Super Admin Dashboard</h1>
            <p class="text-gray-500 text-sm mt-1">{{ now()->format('d-MMMM Y') }} - Bugungi holat</p>
        </div>
        <div class="text-sm text-gray-500 bg-white px-4 py-2 rounded-lg shadow-sm">
            <i class="fas fa-clock text-blue-500 mr-1"></i>
            <span id="clock"></span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Jami tashkilotlar</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_organizations'] }}</p>
                    <p class="text-xs text-green-600 mt-1">
                        <i class="fas fa-check-circle"></i>
                        {{ $stats['active_organizations'] }} faol
                    </p>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Jami foydalanuvchilar</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_users'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Barcha rollar</p>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Bugungi sotuvlar</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['today_orders'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">ta chek</p>
                </div>
                <div class="w-14 h-14 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-receipt text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Bugungi tushum</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($stats['today_sales'], 0, '.', ' ') }}</p>
                    <p class="text-xs text-gray-400 mt-1">so'm</p>
                </div>
                <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Oxirgi 7 kunlik sotuv</h3>
                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">So'm</span>
            </div>
            <canvas id="salesChart" height="100"></canvas>
        </div>

        <!-- Recent Organizations -->
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Yangi tashkilotlar</h3>
                <a href="{{ route('super.organizations.index') }}" class="text-xs text-blue-500 hover:underline">Barchasi</a>
            </div>
            <div class="space-y-3">
                @forelse($recentOrgs as $org)
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-building text-blue-600 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $org->name }}</p>
                        <p class="text-xs text-gray-400">{{ $org->phone }}</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $org->is_active ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                        {{ $org->is_active ? 'Faol' : 'Bloklangan' }}
                    </span>
                </div>
                @empty
                <p class="text-center text-gray-400 text-sm py-4">Hali tashkilotlar yo'q</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Month stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-5 text-white">
            <p class="text-blue-200 text-sm">Oylik tushum</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($stats['month_sales'], 0, '.', ' ') }}</p>
            <p class="text-blue-200 text-xs mt-1">so'm / {{ now()->format('F Y') }}</p>
        </div>
        <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl p-5 text-white">
            <p class="text-green-200 text-sm">Faol smenalar</p>
            <p class="text-3xl font-bold mt-1">{{ $stats['active_shifts'] }}</p>
            <p class="text-green-200 text-xs mt-1">hozir ochiq</p>
        </div>
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl p-5 text-white">
            <p class="text-purple-200 text-sm">Tizim versiyasi</p>
            <p class="text-3xl font-bold mt-1">v1.0.0</p>
            <p class="text-purple-200 text-xs mt-1">YesPOS Backend</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Clock
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent = now.toLocaleTimeString('uz-UZ');
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Sales Chart
    const chartData = @json($salesChart);
    const labels = chartData.map(d => d.date);
    const totals = chartData.map(d => d.total);

    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sotuv (so\'m)',
                data: totals,
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' so\'m';
                        }
                    }
                },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush
