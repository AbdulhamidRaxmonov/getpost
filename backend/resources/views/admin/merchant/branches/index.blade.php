@extends('admin.layouts.app')
@section('title', 'Filiallar')
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Filiallar</h1>
        <a href="{{ route('merchant.branches.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium"><i class="fas fa-plus mr-1"></i> Yangi filial</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($branches as $branch)
        <div class="bg-white rounded-xl shadow-sm p-5 border {{ $branch->is_main ? 'border-blue-200' : 'border-gray-100' }}">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center"><i class="fas fa-store text-blue-600"></i></div>
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $branch->name }}</h3>
                        @if($branch->is_main)<span class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full">Asosiy</span>@endif
                    </div>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $branch->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $branch->is_active ? 'Faol' : 'Nofaol' }}</span>
            </div>
            <div class="mt-3 space-y-1 text-sm text-gray-500">
                @if($branch->phone)<p><i class="fas fa-phone mr-2 text-xs"></i>{{ $branch->phone }}</p>@endif
                @if($branch->address)<p><i class="fas fa-map-marker-alt mr-2 text-xs"></i>{{ $branch->address }}</p>@endif
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-gray-400">
                <span><i class="fas fa-desktop mr-1"></i>{{ $branch->terminals_count }} terminal</span>
                <span><i class="fas fa-users mr-1"></i>{{ $branch->users_count }} hodim</span>
            </div>
            <div class="mt-3 flex gap-2">
                <a href="{{ route('merchant.branches.edit', $branch) }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-1.5 rounded-lg text-xs font-medium"><i class="fas fa-edit mr-1"></i>Tahrirlash</a>
                <form action="{{ route('merchant.branches.destroy', $branch) }}" method="POST" class="flex-1" onsubmit="return confirm('O\'chirilsinmi?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-600 py-1.5 rounded-lg text-xs font-medium"><i class="fas fa-trash-alt mr-1"></i>O'chirish</button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-10 text-gray-400">Filiallar topilmadi</div>
        @endforelse
    </div>
</div>
@endsection
