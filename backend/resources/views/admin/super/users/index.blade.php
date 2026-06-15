@extends('admin.layouts.app')
@section('title', 'Foydalanuvchilar')
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Foydalanuvchilar</h1>
        <a href="{{ route('super.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
            <i class="fas fa-plus mr-1"></i> Yangi
        </a>
    </div>
    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ism yoki telefon..."
               class="flex-1 min-w-48 px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="role" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Barcha rollar</option>
            <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            <option value="org_admin" {{ request('role') === 'org_admin' ? 'selected' : '' }}>Admin</option>
            <option value="cashier" {{ request('role') === 'cashier' ? 'selected' : '' }}>Kassir</option>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Filter</button>
    </form>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">F.I.O</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Telefon</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Rol</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Tashkilot</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Holat</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-4 text-sm font-medium text-gray-800">{{ $user->name }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $user->phone }}</td>
                    <td class="px-5 py-4">
                        @php $roleMap = ['super_admin' => 'bg-red-100 text-red-700', 'org_admin' => 'bg-purple-100 text-purple-700', 'cashier' => 'bg-gray-100 text-gray-700']; @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $roleMap[$user->role] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ['super_admin' => 'Super Admin', 'org_admin' => 'Admin', 'cashier' => 'Kassir'][$user->role] ?? $user->role }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-500">{{ $user->organization?->name ?? '—' }}</td>
                    <td class="px-5 py-4 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $user->is_active ? 'Faol' : 'Bloklangan' }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <a href="{{ route('super.users.edit', $user) }}" class="text-yellow-500 hover:text-yellow-700 p-1.5 hover:bg-yellow-50 rounded transition">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('super.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('O\'chirilsinmi?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 p-1.5 hover:bg-red-50 rounded transition">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Foydalanuvchilar topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $users->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
