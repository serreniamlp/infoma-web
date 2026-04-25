@extends('layouts.app')
@section('title', 'Kelola Acara — Admin EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Acara</h1>
                <p class="text-gray-500 text-sm mt-1">Moderasi semua event dari provider</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
                <div class="h-10 w-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    <p class="text-xs text-gray-500">Total Acara</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
                <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
                    <p class="text-xs text-gray-500">Aktif & Buka</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
                <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-calendar-times text-gray-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['expired'] }}</p>
                    <p class="text-xs text-gray-500">Sudah Lewat</p>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-4">
            <form method="GET" class="flex gap-3 flex-wrap">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama acara, lokasi, provider..."
                       class="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif & Buka</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Sudah Lewat</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                    <i class="fas fa-search mr-1"></i>Cari
                </button>
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('admin.activities.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Reset</a>
                @endif
            </form>
        </div>

        {{-- Tabel --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acara</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendaftar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($activities as $activity)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $activity->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $activity->location }}</p>
                                        @if($activity->price)
                                            <p class="text-xs text-indigo-600 font-medium mt-0.5">Rp {{ number_format($activity->price, 0, ',', '.') }}</p>
                                        @else
                                            <p class="text-xs text-green-600 font-medium mt-0.5">Gratis</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">{{ $activity->provider->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity->provider->email ?? '' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if($activity->registration_deadline)
                                        <p class="text-sm {{ $activity->registration_deadline < now() ? 'text-red-600' : 'text-gray-700' }}">
                                            {{ $activity->registration_deadline->format('d M Y') }}
                                        </p>
                                        @if($activity->registration_deadline < now())
                                            <p class="text-xs text-red-500">Sudah lewat</p>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-sm">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-gray-900">{{ $activity->bookings_count }}</span>
                                    @if($activity->max_participants)
                                        <span class="text-xs text-gray-400"> / {{ $activity->max_participants }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($activity->is_active && $activity->registration_deadline > now())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>Aktif
                                        </span>
                                    @elseif(!$activity->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>Nonaktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            Kedaluwarsa
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.activities.show', $activity) }}"
                                           class="px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>
                                        <form method="POST" action="{{ route('admin.activities.toggleStatus', $activity) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors
                                                           {{ $activity->is_active ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}"
                                                    onclick="return confirm('{{ $activity->is_active ? 'Nonaktifkan' : 'Aktifkan' }} acara ini?')">
                                                <i class="fas {{ $activity->is_active ? 'fa-eye-slash' : 'fa-eye' }} mr-1"></i>
                                                {{ $activity->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.activities.destroy', $activity) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium bg-red-50 text-red-700 rounded-lg hover:bg-red-100"
                                                    onclick="return confirm('Hapus acara {{ $activity->name }}?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-calendar-alt text-4xl mb-3 block opacity-30"></i>
                                    Tidak ada acara ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($activities->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">{{ $activities->links() }}</div>
            @endif
        </div>

    </div>
</div>
@endsection
