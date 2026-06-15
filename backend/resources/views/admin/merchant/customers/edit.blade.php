@extends('admin.layouts.app')
@section('title', 'Mijoz')
@section('content')
<div class="max-w-lg"><div class="mb-5"><a href="{{ route('merchant.customers.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a></div>
<div class="bg-white rounded-xl shadow-sm p-6"><p class="text-gray-500">Bu sahifa ishlab chiqilmoqda.</p>
<a href="{{ route('merchant.customers.index') }}" class="inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Orqaga</a></div></div>
@endsection
