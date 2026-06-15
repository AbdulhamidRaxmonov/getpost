@extends('admin.layouts.app')
@section('title', 'Yangi kategoriya')
@section('content')
<div class="max-w-lg">
    <div class="mb-5 flex items-center gap-3">
        <a href="{{ route('merchant.categories.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-gray-800">Yangi kategoriya</h1>
    </div>
    <form action="{{ route('merchant.categories.store') }}" method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Nomi *</label>
            <input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Rang</label>
            <input type="color" name="color" value="#3B82F6" class="h-10 w-20 border border-gray-200 rounded-lg cursor-pointer"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Tartib raqami</label>
            <input type="number" name="sort_order" value="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" checked class="w-4 h-4">
            <span class="text-sm font-medium text-gray-700">Faol</span></label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium">Saqlash</button>
            <a href="{{ route('merchant.categories.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-200">Bekor</a>
        </div>
    </form>
</div>
@endsection
