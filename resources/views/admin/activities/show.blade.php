@extends('layouts.app')
@section('title', 'Detail Acara — Admin EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.activities.index') }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $activity->name }}</h1>
                <p class="text-gray-500 text-sm mt-0.5">{{ $activity->location }}</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                {{-- Info Utama --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="font-semibold text-gray-900">Informasi Acara</h2>
                        @if($activity->is_active && $activity->registration_deadline > now())
                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700">Aktif & Buka</span>
                        @elseif(!$activity->is_active)
                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">Nonaktif</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">Kedaluwarsa</span>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Harga Pendaftaran</p>
                            @if($activity->price)
                                <p class="font-semibold text-indigo-700 text-lg">Rp {{ number_format($activity->price, 0, ',', '.') }}</p>
                            @else
                                <p class="font-semibold text-green-700">Gratis</p>
                            @endif
                        </div>
                        @if($activity->registration_deadline)
                            <div>
                                <p class="text-gray-500">Deadline Pendaftaran</p>
                                <p class="font-medium {{ $activity->registration_deadline < now() ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $activity->registration_deadline->format('d M Y, H:i') }}
                                </p>
                            </div>
                        @endif
                        @if($activity->max_participants)
                            <div>
                                <p class="text-gray-500">Kuota Peserta</p>
                                <p class="font-medium text-gray-900">
                                    {{ $activity->bookings->count() }} / {{ $activity->max_participants }}
                                </p>
                            </div>
                        @endif
                        <div>
                            <p class="text-gray-500">Ditambahkan</p>
                            <p class="font-medium text-gray-900">{{ $activity->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    @if($activity->description)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-gray-500 text-sm mb-1">Deskripsi</p>
                            <p class="text-gray-700 text-sm">{{ $activity->description }}</p>
                        </div>
                    @endif
                </div>

                {{-- Daftar Pendaftar --}}
                <div class="bg-white rounded-xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Peserta Terdaftar ({{ $activity->bookings->count() }})</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($activity->bookings->take(15) as $booking)
                            <div class="px-6 py-3 flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $booking->user->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">{{ $booking->created_at->format('d M Y') }}</p>
                                </div>
                                @php
                                    $bc = match($booking->status) {
                                        'pending'   => 'bg-orange-100 text-orange-700',
                                        'approved'  => 'bg-blue-100 text-blue-700',
                                        'completed' => 'bg-green-100 text-green-700',
                                        'rejected'  => 'bg-red-100 text-red-700',
                                        default     => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $bc }}">{{ ucfirst($booking->status) }}</span>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-gray-400 text-sm">Belum ada pendaftar</div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-900 mb-3">Provider</h3>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-indigo-700 font-semibold">{{ substr($activity->provider->name ?? 'P', 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $activity->provider->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $activity->provider->email ?? '' }}</p>
                        </div>
                    </div>
                    @if($activity->provider)
                        <a href="{{ route('admin.users.show', $activity->provider) }}"
                           class="text-xs text-blue-600 hover:underline">Lihat profil provider →</a>
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-2">
                    <h3 class="font-semibold text-gray-900 mb-3">Aksi Admin</h3>
                    <form method="POST" action="{{ route('admin.activities.toggleStatus', $activity) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="w-full py-2 px-4 rounded-lg text-sm font-medium transition-colors
                                       {{ $activity->is_active ? 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200' : 'bg-green-50 text-green-700 hover:bg-green-100 border border-green-200' }}"
                                onclick="return confirm('{{ $activity->is_active ? 'Nonaktifkan' : 'Aktifkan' }} acara ini?')">
                            <i class="fas {{ $activity->is_active ? 'fa-eye-slash' : 'fa-eye' }} mr-2"></i>
                            {{ $activity->is_active ? 'Nonaktifkan Acara' : 'Aktifkan Acara' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.activities.destroy', $activity) }}">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full py-2 px-4 rounded-lg text-sm font-medium bg-red-600 text-white hover:bg-red-700 transition-colors"
                                onclick="return confirm('Hapus acara ini secara permanen?')">
                            <i class="fas fa-trash mr-2"></i>Hapus Permanen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
