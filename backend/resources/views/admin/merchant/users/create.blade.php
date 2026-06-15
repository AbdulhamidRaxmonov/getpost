@extends('admin.layouts.app')
@section('title', 'Yangi hodim')
@section('content')
<div class="max-w-2xl">
    <div class="mb-5 flex items-center gap-3">
        <a href="{{ route('merchant.users.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-gray-800">Yangi hodim</h1>
    </div>
    <form action="{{ route('merchant.users.store') }}" method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">F.I.O *</label>
                <input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Telefon *</label>
                <input type="text" name="phone" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Parol *</label>
                <input type="password" name="password" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">PIN kod (4 ta raqam)</label>
                <input type="text" name="pin" maxlength="4" pattern="[0-9]{4}" placeholder="1234" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                <select name="role" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="cashier">Kassir</option>
                    <option value="org_admin">Admin</option>
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Filial</label>
                <select name="branch_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tanlang...</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select></div>
        </div>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" checked class="w-4 h-4">
            <span class="text-sm font-medium text-gray-700">Faol</span></label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium">Saqlash</button>
            <a href="{{ route('merchant.users.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-200">Bekor</a>
        </div>
    </form>
</div>
@endsection
