@extends('admin.layouts.app')
@section('title', 'Foydalanuvchi')
@section('content')
<div class="max-w-lg"><a href="{{ route('super.users.index') }}" class="text-gray-400"><i class="fas fa-arrow-left"></i></a>
<div class="bg-white rounded-xl shadow-sm p-6 mt-5"><h2 class="text-xl font-bold">{{ $user->name }}</h2></div></div>
@endsection
