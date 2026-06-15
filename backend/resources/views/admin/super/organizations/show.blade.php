@extends('admin.layouts.app')

@section('title', $organization->name)
@section('breadcrumb', 'Super Admin / Tashkilotlar / ' . $organization->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('super.organizations.index') }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $organization->name }}</h1>
                <p class="text-gray-500 text-sm">{{ $organization->legal_name }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('super.organizations.edit', $organization) }}"
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-edit mr-1"></i> Tahrirlash
            </a>
            <form action="{{ route('super.organizations.toggle-status', $organization) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="{{ $organization->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas {{ $organization->is_active ? 'fa-ban' : 'fa-check-circle' }} mr-1"></i>
                    {{ $organization->is_active ? 'Bloklash' : 'Faollashtirish' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm text-center">
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_orders']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Jami savdolar</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm text-center">
            <p class="text-xl font-bold text-green-600">{{ number_format($stats['total_sales'], 0, '.', ' ') }}</p>
            <p class="text-xs text-gray-500 mt-1">Jami tushum (so'm)</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $stats['products_count'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Mahsulotlar</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm text-center">
            <p class="text-xl font-bold text-yellow-600">{{ number_format($stats['today_sales'], 0, '.', ' ') }}</p>
            <p class="text-xs text-gray-500 mt-1">Bugungi tushum</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Info -->
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Tashkilot ma'lumotlari</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <dt class="text-gray-500">Telefon</dt>
                    <dd class="font-medium">{{ $organization->phone }}</dd>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium">{{ $organization->email ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <dt class="text-gray-500">STIR</dt>
                    <dd class="font-medium">{{ $organization->tin ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <dt class="text-gray-500">Manzil</dt>
                    <dd class="font-medium">{{ $organization->address ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <dt class="text-gray-500">Tarif</dt>
                    <dd><span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs font-medium capitalize">{{ $organization->subscription_plan }}</span></dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Tugash sanasi</dt>
                    <dd class="font-medium">{{ $organization->subscription_expires_at?->format('d.m.Y') ?? 'Cheksiz' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Branches -->
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Filiallar va terminallar</h3>
            <div class="space-y-3">
                @foreach($organization->branches as $branch)
                <div class="border border-gray-100 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-store text-blue-500"></i>
                            <span class="font-medium text-sm">{{ $branch->name }}</span>
                            @if($branch->is_main)
                            <span class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full">Asosiy</span>
                            @endif
                        </div>
                        <span class="text-xs {{ $branch->is_active ? 'text-green-600' : 'text-red-500' }}">
                            {{ $branch->is_active ? 'Faol' : 'Nofaol' }}
                        </span>
                    </div>
                    @if($branch->terminals->count() > 0)
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach($branch->terminals as $terminal)
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">
                            <i class="fas fa-desktop mr-1"></i>{{ $terminal->name }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Users -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Foydalanuvchilar</h3>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">F.I.O</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Telefon</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Rol</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Holat</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Oxirgi kirish</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($organization->users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-sm font-medium text-gray-800">{{ $user->name }}</td>
                    <td class="px-5 py-3 text-sm text-gray-600">{{ $user->phone }}</td>
                    <td class="px-5 py-3">
                        @php $roleMap = ['org_admin' => ['bg-purple-100 text-purple-700', 'Admin'], 'cashier' => ['bg-gray-100 text-gray-700', 'Kassir']]; @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $roleMap[$user->role][0] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $roleMap[$user->role][1] ?? $user->role }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs {{ $user->is_active ? 'text-green-600' : 'text-red-500' }}">
                            <i class="fas fa-circle text-xs mr-1"></i>
                            {{ $user->is_active ? 'Faol' : 'Bloklangan' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-400">{{ $user->last_login_at?->diffForHumans() ?? 'Hech qachon' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
