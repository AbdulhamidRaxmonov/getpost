@extends('admin.layouts.app')
@section('title', $branch->name)
@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('merchant.branches.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $branch->name }}</h1>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b"><dt class="text-gray-500">Telefon</dt><dd>{{ $branch->phone ?? '—' }}</dd></div>
            <div class="flex justify-between py-2 border-b"><dt class="text-gray-500">Manzil</dt><dd>{{ $branch->address ?? '—' }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-gray-500">Holat</dt>
                <dd><span class="text-xs px-2 py-0.5 rounded-full {{ $branch->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $branch->is_active ? 'Faol' : 'Nofaol' }}</span></dd></div>
        </dl>
    </div>
</div>
@endsection
