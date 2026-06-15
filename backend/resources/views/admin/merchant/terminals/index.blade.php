@extends('admin.layouts.app')
@section('title', 'Terminallar')
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Terminallar (Kassalar)</h1>
        <a href="{{ route('merchant.terminals.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium"><i class="fas fa-plus mr-1"></i> Yangi terminal</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($terminals as $terminal)
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg {{ $terminal->currentShift ? 'bg-green-100' : 'bg-gray-100' }} flex items-center justify-center">
                        <i class="fas fa-desktop {{ $terminal->currentShift ? 'text-green-600' : 'text-gray-400' }}"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $terminal->name }}</h3>
                        <p class="text-xs text-gray-400">{{ $terminal->branch?->name }}</p>
                    </div>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $terminal->currentShift ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $terminal->currentShift ? 'Smena ochiq' : 'Yopiq' }}
                </span>
            </div>
            @if($terminal->currentShift)
            <div class="mt-3 text-xs text-gray-500 bg-green-50 rounded-lg p-2">
                <i class="fas fa-user mr-1"></i>{{ $terminal->currentShift->user?->name ?? '—' }}
                <span class="ml-2"><i class="fas fa-clock mr-1"></i>{{ $terminal->currentShift->opened_at?->format('H:i') }}</span>
            </div>
            @endif
            <div class="mt-3 flex gap-2">
                <a href="{{ route('merchant.terminals.edit', $terminal) }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-1.5 rounded-lg text-xs font-medium"><i class="fas fa-edit mr-1"></i>Tahrirlash</a>
                <form action="{{ route('merchant.terminals.destroy', $terminal) }}" method="POST" class="flex-1" onsubmit="return confirm('O\'chirilsinmi?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-600 py-1.5 rounded-lg text-xs font-medium"><i class="fas fa-trash-alt mr-1"></i>O'chirish</button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-10 text-gray-400">Terminallar topilmadi</div>
        @endforelse
    </div>
</div>
@endsection
