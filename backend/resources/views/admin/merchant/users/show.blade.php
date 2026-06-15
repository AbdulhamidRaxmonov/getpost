@extends('admin.layouts.app')
@section('title', $user->name)
@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('merchant.users.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b"><dt class="text-gray-500">Telefon</dt><dd>{{ $user->phone }}</dd></div>
            <div class="flex justify-between py-2 border-b"><dt class="text-gray-500">Email</dt><dd>{{ $user->email ?? '—' }}</dd></div>
            <div class="flex justify-between py-2 border-b"><dt class="text-gray-500">Rol</dt>
                <dd><span class="text-xs px-2 py-0.5 rounded-full {{ $user->role === 'org_admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700' }}">{{ $user->role === 'org_admin' ? 'Admin' : 'Kassir' }}</span></dd></div>
            <div class="flex justify-between py-2 border-b"><dt class="text-gray-500">Filial</dt><dd>{{ $user->branch?->name ?? '—' }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-gray-500">Holat</dt>
                <dd><span class="text-xs px-2 py-0.5 rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $user->is_active ? 'Faol' : 'Bloklangan' }}</span></dd></div>
        </dl>
    </div>
</div>
@endsection
