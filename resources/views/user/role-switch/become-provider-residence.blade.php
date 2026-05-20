@extends('layouts.app')
@section('title', 'Daftar Sebagai Penjual Kos-Kosan — INFOMA')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        @if($status === 'pending')
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-8 py-10 text-center">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clock text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Pengajuan Sedang Ditinjau</h1>
                <p class="text-orange-100">Tim admin INFOMA sedang memeriksa data kamu</p>
            </div>
            <div class="px-8 py-8">
                <div class="bg-orange-50 border border-orange-200 rounded-xl p-5 mb-6">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-orange-500 mt-0.5 text-lg"></i>
                        <div>
                            <h3 class="font-semibold text-orange-800 mb-2">Status Pengajuan Penjual Kos</h3>
                            <ul class="text-sm text-orange-700 space-y-1.5">
                                <li class="flex items-center gap-2"><i class="fas fa-check text-orange-400"></i>NIK & nama sudah diterima</li>
                                <li class="flex items-center gap-2"><i class="fas fa-check text-orange-400"></i>Foto KTP sudah diterima</li>
                                <li class="flex items-center gap-2"><i class="fas fa-check text-orange-400"></i>Foto selfie sudah diterima</li>
                                <li class="flex items-center gap-2"><i class="fas fa-clock text-orange-400"></i>Menunggu verifikasi admin (1×24 jam)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <a href="{{ route('user.profile.show') }}"
                   class="w-full flex items-center justify-center gap-2 py-3 px-6 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                    <i class="fas fa-arrow-left"></i>Kembali ke Profil
                </a>
            </div>
        </div>

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
                @if(auth()->user()->provider_rejection_reason)
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                        <p class="text-sm font-semibold text-red-800 mb-1">Alasan Penolakan:</p>
                        <p class="text-sm text-red-700">{{ auth()->user()->provider_rejection_reason }}</p>
                    </div>
                @endif
                @include('user.role-switch._provider-form', [
                    'action'      => route('role.switch.become.provider_residence.submit'),
                    'submitLabel' => 'Kirim Ulang Pengajuan Penjual Kos',
                ])
            </div>
        </div>

        @else
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-teal-600 to-teal-700 px-8 py-10 text-center">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-home text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Daftar sebagai Penjual Kos-Kosan</h1>
                <p class="text-teal-100">Verifikasi identitasmu untuk mulai menyewakan hunian</p>
            </div>
            <div class="px-8 py-8">
                <div class="grid grid-cols-3 gap-3 mb-8">
                    <div class="text-center p-3 bg-teal-50 rounded-xl">
                        <i class="fas fa-building text-teal-500 text-xl mb-1 block"></i>
                        <p class="text-xs text-gray-600 font-medium">Listing Hunian</p>
                    </div>
                    <div class="text-center p-3 bg-green-50 rounded-xl">
                        <i class="fas fa-shield-alt text-green-500 text-xl mb-1 block"></i>
                        <p class="text-xs text-gray-600 font-medium">Booking Aman</p>
                    </div>
                    <div class="text-center p-3 bg-blue-50 rounded-xl">
                        <i class="fas fa-chart-line text-blue-500 text-xl mb-1 block"></i>
                        <p class="text-xs text-gray-600 font-medium">Kelola Mudah</p>
                    </div>
                </div>
                <div class="border-t border-gray-100 mb-6"></div>
                @include('user.role-switch._provider-form', [
                    'action'      => route('role.switch.become.provider_residence.submit'),
                    'submitLabel' => 'Kirim Pengajuan Penjual Kos',
                ])
            </div>
        </div>
        @endif

        <div class="text-center mt-6">
            <a href="{{ route('user.profile.show') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Profil
            </a>
        </div>
    </div>
</div>
@endsection
