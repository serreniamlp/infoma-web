{{-- resources/views/user/marketplace/become-seller.blade.php --}}

@extends('layouts.app')
@section('title', 'Mulai Berjualan — EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ============================================================ --}}
        {{-- STATUS: PENDING --}}
        {{-- ============================================================ --}}
        @if($status === 'pending')

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-8 py-10 text-center">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clock text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Pengajuan Sedang Ditinjau</h1>
                <p class="text-orange-100">Tim admin EduLiving sedang memeriksa data kamu</p>
            </div>
            <div class="px-8 py-8">
                <div class="bg-orange-50 border border-orange-200 rounded-xl p-5 mb-6">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-orange-500 mt-0.5 text-lg"></i>
                        <div>
                            <h3 class="font-semibold text-orange-800 mb-2">Status Pengajuanmu</h3>
                            <ul class="text-sm text-orange-700 space-y-1.5">
                                <li class="flex items-center gap-2"><i class="fas fa-check text-orange-400"></i>NIK & nama sudah diterima</li>
                                <li class="flex items-center gap-2"><i class="fas fa-check text-orange-400"></i>Foto KTP sudah diterima</li>
                                <li class="flex items-center gap-2"><i class="fas fa-check text-orange-400"></i>Foto selfie sudah diterima</li>
                                <li class="flex items-center gap-2"><i class="fas fa-clock text-orange-400"></i>Menunggu verifikasi admin (1×24 jam)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <a href="{{ route('user.dashboard') }}"
                   class="w-full flex items-center justify-center gap-2 py-3 px-6 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                    <i class="fas fa-arrow-left"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- STATUS: REJECTED --}}
        {{-- ============================================================ --}}
        @elseif($status === 'rejected')

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-red-500 to-red-600 px-8 py-10 text-center">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-times-circle text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Pengajuan Ditolak</h1>
                <p class="text-red-100">Silakan perbaiki dan ajukan ulang</p>
            </div>
            <div class="px-8 py-8">
                @if(auth()->user()->seller_rejection_reason)
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                        <p class="text-sm font-semibold text-red-800 mb-1">Alasan Penolakan:</p>
                        <p class="text-sm text-red-700">{{ auth()->user()->seller_rejection_reason }}</p>
                    </div>
                @endif

                {{-- Form ajukan ulang — sama persis dengan form pertama --}}
                @include('user.marketplace._seller-form')
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- STATUS: NONE — Form pertama kali --}}
        {{-- ============================================================ --}}
        @else

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-10 text-center">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-store text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Mulai Berjualan</h1>
                <p class="text-blue-100">Verifikasi identitasmu untuk mulai jualan</p>
            </div>
            <div class="px-8 py-8">
                <div class="grid grid-cols-3 gap-3 mb-8">
                    <div class="text-center p-3 bg-blue-50 rounded-xl">
                        <i class="fas fa-users text-blue-500 text-xl mb-1 block"></i>
                        <p class="text-xs text-gray-600 font-medium">Jangkauan Luas</p>
                    </div>
                    <div class="text-center p-3 bg-green-50 rounded-xl">
                        <i class="fas fa-shield-alt text-green-500 text-xl mb-1 block"></i>
                        <p class="text-xs text-gray-600 font-medium">Transaksi Aman</p>
                    </div>
                    <div class="text-center p-3 bg-purple-50 rounded-xl">
                        <i class="fas fa-chart-line text-purple-500 text-xl mb-1 block"></i>
                        <p class="text-xs text-gray-600 font-medium">Dashboard Lengkap</p>
                    </div>
                </div>

                <div class="border-t border-gray-100 mb-6"></div>

                @include('user.marketplace._seller-form')
            </div>
        </div>

        @endif

        <div class="text-center mt-6">
            <a href="{{ route('marketplace.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Marketplace
            </a>
        </div>
    </div>
</div>
@endsection