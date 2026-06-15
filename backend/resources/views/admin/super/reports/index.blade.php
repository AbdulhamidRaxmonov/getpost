@extends('admin.layouts.app')
@section('title', 'Hisobotlar')
@section('content')
<div class="space-y-5">
    <h1 class="text-2xl font-bold text-gray-800">Umumiy hisobot</h1>
    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-3">
        <div><label class="block text-xs text-gray-500 mb-1">Dan</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Gacha</label>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Hisobot</button>
        </div>
    </form>
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm text-center border-t-4 border-blue-500">
            <p class="text-3xl font-bold text-gray-800">{{ number_format($summary->total_orders) }}</p>
            <p class="text-sm text-gray-500 mt-1">Jami savdolar</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm text-center border-t-4 border-green-500">
            <p class="text-2xl font-bold text-gray-800">{{ number_format($summary->total_sales, 0, '.', ' ') }}</p>
            <p class="text-sm text-gray-500 mt-1">Jami tushum (so'm)</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Sotuv grafigi</h3>
        <canvas id="chart" height="80"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-800">Tashkilotlar bo'yicha</h3></div>
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Tashkilot</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Savdolar</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Tushum</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($byOrg as $org)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-sm font-medium text-gray-800">{{ $org->name }}</td>
                    <td class="px-5 py-3 text-right text-sm text-gray-700">{{ number_format($org->orders_count) }}</td>
                    <td class="px-5 py-3 text-right text-sm font-semibold text-gray-800">{{ number_format($org->total_sales, 0, '.', ' ') }} so'm</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
    const d = @json($daily);
    new Chart(document.getElementById('chart').getContext('2d'), {
        type: 'line',
        data: { labels: d.map(x => x.date), datasets: [{ label: "Sotuv", data: d.map(x => x.sales), borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', tension: 0.4, fill: true }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } }
    });
</script>
@endpush
