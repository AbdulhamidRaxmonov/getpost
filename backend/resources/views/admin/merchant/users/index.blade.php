@extends('admin.layouts.app')
@section('title', 'Hodimlar')
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Hodimlar</h1>
        <a href="{{ route('merchant.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
            <i class="fas fa-plus mr-1"></i> Yangi hodim
        </a>
    </div>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">F.I.O</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Telefon</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Rol</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Filial</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">PIN</th>
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
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $user->role === 'org_admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $user->role === 'org_admin' ? 'Admin' : 'Kassir' }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $user->branch?->name ?? '—' }}</td>
                    <td class="px-5 py-4 text-center">
                        @if($user->pin)
                        <span class="font-mono text-sm bg-gray-100 px-2 py-0.5 rounded">****</span>
                        @else
                        <span class="text-xs text-gray-400">Yo'q</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $user->is_active ? 'Faol' : 'Bloklangan' }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('merchant.users.edit', $user) }}" class="text-yellow-500 hover:text-yellow-700 p-1.5 hover:bg-yellow-50 rounded transition"><i class="fas fa-edit"></i></a>
                            <button onclick="document.getElementById('pin-form-{{ $user->id }}').classList.toggle('hidden')"
                                    class="text-blue-500 hover:text-blue-700 p-1.5 hover:bg-blue-50 rounded transition" title="PIN o'zgartirish">
                                <i class="fas fa-key"></i>
                            </button>
                            <form action="{{ route('merchant.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('O\'chirilsinmi?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 p-1.5 hover:bg-red-50 rounded transition"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </div>
                        <div id="pin-form-{{ $user->id }}" class="hidden mt-2">
                            <form action="{{ route('merchant.users.reset-pin', $user) }}" method="POST" class="flex gap-2">
                                @csrf
                                <input type="text" name="pin" maxlength="4" pattern="[0-9]{4}" placeholder="PIN" class="w-16 px-2 py-1 border border-gray-200 rounded text-sm font-mono text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600">OK</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">Hodimlar topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
