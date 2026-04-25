@extends('layouts.app')
@section('title', 'Kelola Pengguna — Admin EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Pengguna</h1>
                <p class="text-gray-500 text-sm mt-1">Manajemen semua akun pengguna EduLiving</p>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-2 text-green-700">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-2 text-red-700">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Tab Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="flex overflow-x-auto border-b border-gray-200">
                @php
                    $tabs = [
                        ['key' => '',                 'label' => 'Semua',              'icon' => 'fa-users',          'count' => $counts['all']],
                        ['key' => 'user',             'label' => 'Mahasiswa',          'icon' => 'fa-user-graduate',  'count' => $counts['user']],
                        ['key' => 'provider_residence','label' => 'Provider Hunian',   'icon' => 'fa-building',       'count' => $counts['provider_residence']],
                        ['key' => 'provider_event',   'label' => 'Provider Acara',     'icon' => 'fa-calendar-alt',   'count' => $counts['provider_event']],
                        ['key' => 'seller',           'label' => 'Seller FJB',         'icon' => 'fa-store',          'count' => $counts['seller']],
                        ['key' => 'pending_seller',   'label' => 'Pengajuan Seller',   'icon' => 'fa-id-card',        'count' => $counts['pending_seller'],   'badge' => true],
                        ['key' => 'pending_provider', 'label' => 'Pengajuan Provider', 'icon' => 'fa-user-clock',     'count' => $counts['pending_provider'], 'badge' => true],
                    ];
                    $activeRole = request('role', '');
                @endphp

                @foreach($tabs as $tab)
                    <a href="{{ request()->fullUrlWithQuery(['role' => $tab['key'], 'page' => null]) }}"
                       class="flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors
                              {{ $activeRole === $tab['key']
                                  ? 'border-blue-600 text-blue-600'
                                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas {{ $tab['icon'] }}"></i>
                        {{ $tab['label'] }}
                        <span class="px-2 py-0.5 rounded-full text-xs
                                     {{ (!empty($tab['badge']) && $tab['count'] > 0) ? 'bg-red-100 text-red-700 font-semibold' : 'bg-gray-100 text-gray-600' }}">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Search & Filter bar --}}
            <div class="p-4">
                <form method="GET" class="flex gap-3 flex-wrap">
                    <input type="hidden" name="role" value="{{ request('role') }}">
                    <div class="flex-1 min-w-48">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari nama, email, telepon..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-1"></i>Cari
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.users.index', ['role' => request('role')]) }}"
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition-colors">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengajuan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terdaftar</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                                            <span class="text-white text-sm font-semibold">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            @php
                                                $roleColor = match($role->name) {
                                                    'admin'              => 'bg-purple-100 text-purple-700',
                                                    'provider_residence' => 'bg-blue-100 text-blue-700',
                                                    'provider_event'     => 'bg-indigo-100 text-indigo-700',
                                                    'user'               => 'bg-green-100 text-green-700',
                                                    default              => 'bg-gray-100 text-gray-600',
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $roleColor }}">
                                                {{ $role->display_name ?? $role->name }}
                                            </span>
                                        @endforeach
                                        @if($user->is_seller)
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                                <i class="fas fa-store mr-0.5"></i>Seller
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->is_active ?? true)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->seller_status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                            <i class="fas fa-clock text-xs"></i>Seller Pending
                                        </span>
                                    @elseif($user->provider_status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                            <i class="fas fa-clock text-xs"></i>Provider Pending
                                        </span>
                                    @elseif($user->seller_status === 'rejected' || $user->provider_status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                            <i class="fas fa-times-circle text-xs"></i>Ditolak
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                           class="px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>
                                        <form method="POST" action="{{ route('admin.users.toggleStatus', $user) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors
                                                           {{ ($user->is_active ?? true) ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}"
                                                    onclick="return confirm('{{ ($user->is_active ?? true) ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $user->name }}?')">
                                                <i class="fas {{ ($user->is_active ?? true) ? 'fa-ban' : 'fa-check' }} mr-1"></i>
                                                {{ ($user->is_active ?? true) ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-users text-4xl mb-3 block opacity-30"></i>
                                    Tidak ada pengguna ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
