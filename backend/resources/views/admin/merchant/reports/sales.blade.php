@extends('admin.layouts.app')

@section('title', 'Sotuv hisoboti')
@section('breadcrumb', 'Hisobotlar / Sotuv')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Sotuv hisoboti</h1>
    </div>

    <!-- Date Filter -->
    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Dan</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Gacha</label>
            <input type="date" name="date_to" value="{{ $dateTo }}"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
                <i class="fas fa-chart-bar mr-1"></i> Hisobot
            </button>
        </div>
    </form>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center border-t-4 border-blue-500">
            <p class="text-2xl font-bold text-gray-800">{{ number_format($summary->total_orders) }}</p>
            <p class="text-xs text-gray-500 mt-1">Jami savdolar</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center border-t-4 border-green-500">
            <p class="text-xl font-bold text-gray-800">{{ number_format($summary->total_sales, 0, '.', ' ') }}</p>
            <p class="text-xs text-gray-500 mt-1">Jami tushum (so'm)</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center border-t-4 border-red-400">
            <p class="text-xl font-bold text-gray-800">{{ number_format($summary->total_discount, 0, '.', ' ') }}</p>
            <p class="text-xs text-gray-500 mt-1">Jami chegirma (so'm)</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center border-t-4 border-purple-500">
            <p class="text-xl font-bold text-gray-800">
                @if($summary->total_orders > 0)
                    {{ number_format($summary->total_sales / $summary->total_orders, 0, '.', ' ') }}
                @else 0 @endif
            </p>
            <p class="text-xs text-gray-500 mt-1">O'rtacha chek (so'm)</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Kunlik sotuv grafigi</h3>
            <canvas id="dailyChart" height="100"></canvas>
        </div>

        <!-- By Payment -->
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">To'lov usuli bo'yicha</h3>
            <div class="space-y-3">
                @foreach($byPayment as $p)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-700">{{ $paymentLabels[$p->payment_method] ?? $p->payment_method }}</span>
                        <span class="font-medium">{{ $p->count }} ta</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @php $percent = $summary->total_sales > 0 ? ($p->total / $summary->total_sales) * 100 : 0; @endphp
                        <div class="flex-1 bg-gray-100 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500 w-16 text-right">{{ number_format($p->total, 0, '.', ' ') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Daily table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Kunlik ko'rsatkichlar</h3>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Sana</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Savdolar soni</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Tushum</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($daily as $day)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-sm text-gray-700">{{ \Carbon\Carbon::parse($day->date)->format('d.m.Y, l') }}</td>
                    <td class="px-5 py-3 text-right text-sm font-medium text-gray-800">{{ $day->orders }}</td>
                    <td class="px-5 py-3 text-right text-sm font-semibold text-gray-800">{{ number_format($day->sales, 0, '.', ' ') }} so'm</td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400">Ma'lumot yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const daily = @json($daily);
    new Chart(document.getElementById('dailyChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: daily.map(d => d.date),
            datasets: [{
                label: "Sotuv",
                data: daily.map(d => d.sales),
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: '#3b82f6',
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() }, grid: { color: 'rgba(0,0,0,0.04)' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush
