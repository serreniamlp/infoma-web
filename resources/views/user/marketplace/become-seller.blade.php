@extends('layouts.app')

@section('title', 'Mulai Berjualan - EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-10 text-center">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-store text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Mulai Berjualan</h1>
                <p class="text-blue-100">Jual barang bekasmu kepada sesama mahasiswa</p>
            </div>

            <!-- Benefits -->
            <div class="px-8 py-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 text-center">Keuntungan Berjualan di EduLiving</h2>

                <div class="grid grid-cols-1 gap-4 mb-8">
                    <div class="flex items-start gap-4 p-4 bg-blue-50 rounded-xl">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Jangkauan Luas</h3>
                            <p class="text-sm text-gray-600 mt-1">Produkmu bisa dilihat oleh ribuan mahasiswa di platform EduLiving</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 bg-green-50 rounded-xl">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-shield-alt text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Transaksi Aman</h3>
                            <p class="text-sm text-gray-600 mt-1">Sistem transaksi yang aman dan terpercaya untuk jual beli antar mahasiswa</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 bg-purple-50 rounded-xl">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-boxes text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Kelola Produk Mudah</h3>
                            <p class="text-sm text-gray-600 mt-1">Dashboard penjual yang mudah digunakan untuk kelola produk dan transaksi</p>
                        </div>
                    </div>
                </div>

                <!-- Info sementara -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-8">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-yellow-600 mt-0.5"></i>
                        <div>
                            <h3 class="font-medium text-yellow-800">Informasi</h3>
                            <p class="text-sm text-yellow-700 mt-1">
                                Saat ini aktivasi penjual langsung disetujui secara otomatis.
                                Ke depannya akan ada proses verifikasi identitas untuk keamanan transaksi.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aktivasi -->
                <form method="POST" action="{{ route('user.marketplace.sell.activate') }}">
                    @csrf
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 px-6 rounded-xl font-semibold text-lg transition-colors flex items-center justify-center gap-3">
                        <i class="fas fa-rocket"></i>
                        Aktifkan Akun Penjual Sekarang
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-4">
                    Dengan mengaktifkan, kamu menyetujui
                    <a href="#" class="text-blue-600 hover:underline">syarat dan ketentuan</a>
                    penjual EduLiving
                </p>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('marketplace.index') }}"
               class="text-gray-600 hover:text-gray-900 text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Marketplace
            </a>
        </div>
    </div>
</div>
@endsection