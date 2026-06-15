@extends('admin.layouts.app')

@section('title', 'Yangi tashkilot')
@section('breadcrumb', 'Super Admin / Tashkilotlar / Yangi')

@section('content')
<div class="max-w-3xl">
    <div class="mb-5">
        <a href="{{ route('super.organizations.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Orqaga
        </a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Yangi tashkilot yaratish</h1>
    </div>

    <form action="{{ route('super.organizations.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Organization Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-building text-blue-500"></i>
                Tashkilot ma'lumotlari
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tashkilot nomi <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Yuridik nomi</label>
                    <input type="text" name="legal_name" value="{{ old('legal_name') }}"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="+998901234567"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">STIR (INN)</label>
                    <input type="text" name="tin" value="{{ old('tin') }}"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Manzil</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
            </div>
        </div>

        <!-- Subscription -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-crown text-yellow-500"></i>
                Obuna rejasi
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tarif <span class="text-red-500">*</span></label>
                    <select name="subscription_plan" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="basic" {{ old('subscription_plan') === 'basic' ? 'selected' : '' }}>Basic - Asosiy</option>
                        <option value="pro" {{ old('subscription_plan') === 'pro' ? 'selected' : '' }}>Pro - Kengaytirilgan</option>
                        <option value="enterprise" {{ old('subscription_plan') === 'enterprise' ? 'selected' : '' }}>Enterprise - Korporativ</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tugash sanasi</label>
                    <input type="date" name="subscription_expires_at" value="{{ old('subscription_expires_at') }}"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
            </div>
        </div>

        <!-- Admin Account -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-user-shield text-green-500"></i>
                Admin hisob yaratish
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">F.I.O <span class="text-red-500">*</span></label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon <span class="text-red-500">*</span></label>
                    <input type="text" name="admin_phone" value="{{ old('admin_phone') }}" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parol <span class="text-red-500">*</span></label>
                    <input type="password" name="admin_password" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">POS PIN kodi (4 raqam)</label>
                    <input type="text" name="admin_pin" value="{{ old('admin_pin') }}" maxlength="4" pattern="[0-9]{4}"
                           placeholder="Masalan: 1234"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
                <i class="fas fa-save mr-2"></i> Saqlash
            </button>
            <a href="{{ route('super.organizations.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-medium transition">
                Bekor qilish
            </a>
        </div>
    </form>
</div>
@endsection
