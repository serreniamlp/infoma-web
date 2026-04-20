@extends('layouts.app')

@section('title', 'Profil Saya - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Profil Saya</h1>
                <p class="text-gray-600 mt-2">Kelola informasi akun Anda</p>
            </div>
            <a href="{{ route('user.dashboard') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>

        <!-- Profile Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex items-start">
                <div class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center text-white text-2xl font-bold mr-4">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    @if($user->phone)
                        <p class="text-gray-600 mt-1"><i class="fas fa-phone mr-2"></i>{{ $user->phone }}</p>
                    @endif
                    @if($user->address)
                        <p class="text-gray-600 mt-1"><i class="fas fa-map-marker-alt mr-2"></i>{{ $user->address }}</p>
                    @endif
                    <div class="mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $user->roles->first()->name ?? 'user' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="{{ route('user.bookings.index') }}" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="bg-yellow-100 rounded-lg p-3 mr-4">
                        <i class="fas fa-bookmark text-yellow-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Booking Saya</h3>
                        <p class="text-gray-600 text-sm">Lihat dan kelola booking</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('user.bookmarks.index') }}" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-lg p-3 mr-4">
                        <i class="fas fa-heart text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Bookmark</h3>
                        <p class="text-gray-600 text-sm">Item tersimpan Anda</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection


