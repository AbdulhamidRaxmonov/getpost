@extends('admin.layouts.app')
@section('title', 'Filial tahrirlash')
@section('content')
<div class="max-w-lg">
    <div class="mb-5 flex items-center gap-3">
        <a href="{{ route('merchant.branches.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-gray-800">{{ $branch->name }} - Tahrirlash</h1>
    </div>
    <form action="{{ route('merchant.branches.update', $branch) }}" method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf @method('PUT')
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Filial nomi *</label>
            <input type="text" name="name" value="{{ old('name', $branch->name) }}" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
            <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Manzil</label>
            <input type="text" name="address" value="{{ old('address', $branch->address) }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ $branch->is_active ? 'checked' : '' }} class="w-4 h-4"><span class="text-sm font-medium text-gray-700">Faol</span></label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium">Saqlash</button>
            <a href="{{ route('merchant.branches.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-200">Bekor</a>
        </div>
    </form>
</div>
@endsection
