@extends('admin.layouts.app')
@section('title', 'Tashkilotni tahrirlash')
@section('content')
<div class="max-w-3xl">
    <div class="mb-5 flex items-center gap-3">
        <a href="{{ route('super.organizations.show', $organization) }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $organization->name }} - Tahrirlash</h1>
    </div>
    <form action="{{ route('super.organizations.update', $organization) }}" method="POST" class="space-y-5">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Nomi *</label>
                <input type="text" name="name" value="{{ old('name', $organization->name) }}" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Yuridik nomi</label>
                <input type="text" name="legal_name" value="{{ old('legal_name', $organization->legal_name) }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                <input type="text" name="phone" value="{{ old('phone', $organization->phone) }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $organization->email) }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tarif</label>
                <select name="subscription_plan" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['basic', 'pro', 'enterprise'] as $plan)
                    <option value="{{ $plan }}" {{ $organization->subscription_plan === $plan ? 'selected' : '' }}>{{ ucfirst($plan) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tugash sanasi</label>
                <input type="date" name="subscription_expires_at" value="{{ old('subscription_expires_at', $organization->subscription_expires_at?->format('Y-m-d')) }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div class="md:col-span-2"><label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ $organization->is_active ? 'checked' : '' }} class="w-4 h-4">
                <span class="text-sm font-medium text-gray-700">Faol</span></label></div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium">Saqlash</button>
            <a href="{{ route('super.organizations.show', $organization) }}" class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-200">Bekor</a>
        </div>
    </form>
</div>
@endsection
