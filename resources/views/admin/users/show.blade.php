@extends('layouts.app')
@section('title', 'Detail Pengguna — Admin EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('admin.users.index') }}" class="hover:text-blue-600">Pengguna</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900 font-medium">{{ $user->name }}</span>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-2 text-green-700">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-2 text-red-700">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Kolom kiri: Info user --}}
            <div class="lg:col-span-1 space-y-6">

                {{-- Profil Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="text-center">
                        <div class="h-20 w-20 rounded-full bg-blue-600 flex items-center justify-center mx-auto mb-4">
                            <span class="text-white text-3xl font-bold">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-gray-500 text-sm">{{ $user->email }}</p>
                        <p class="text-gray-500 text-sm mt-1">{{ $user->phone }}</p>

                        <div class="flex flex-wrap justify-center gap-1 mt-3">
                            @foreach($user->roles as $role)
                                @php
                                    $c = match($role->name) {
                                        'admin'              => 'bg-purple-100 text-purple-700',
                                        'provider_residence' => 'bg-blue-100 text-blue-700',
                                        'provider_event'     => 'bg-indigo-100 text-indigo-700',
                                        'user'               => 'bg-green-100 text-green-700',
                                        default              => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $c }}">{{ $role->display_name ?? $role->name }}</span>
                            @endforeach
                            @if($user->is_seller)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700"><i class="fas fa-store mr-0.5"></i>Seller</span>
                            @endif
                        </div>

                        <div class="mt-4">
                            @if($user->is_active ?? true)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700">
                                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>Akun Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">
                                    <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>Akun Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Terdaftar</span>
                            <span class="font-medium">{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                        @if($user->address)
                            <div>
                                <span class="text-gray-500">Alamat</span>
                                <p class="font-medium text-gray-700 mt-1">{{ $user->address }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 space-y-2">
                        <form method="POST" action="{{ route('admin.users.toggleStatus', $user) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="w-full py-2 px-4 rounded-lg text-sm font-medium transition-colors
                                           {{ ($user->is_active ?? true) ? 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200' : 'bg-green-50 text-green-700 hover:bg-green-100 border border-green-200' }}"
                                    onclick="return confirm('{{ ($user->is_active ?? true) ? 'Nonaktifkan' : 'Aktifkan' }} akun ini?')">
                                <i class="fas {{ ($user->is_active ?? true) ? 'fa-ban' : 'fa-check-circle' }} mr-2"></i>
                                {{ ($user->is_active ?? true) ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                            </button>
                        </form>
                        <a href="{{ route('admin.users.activities', $user) }}"
                           class="w-full block text-center py-2 px-4 rounded-lg text-sm font-medium bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200 transition-colors">
                            <i class="fas fa-history mr-2"></i>Lihat Aktivitas
                        </a>
                    </div>
                </div>
            </div>

            {{-- Kolom kanan: Approval --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Approval Seller FJB --}}
                @if($user->seller_status && $user->seller_status !== 'none')
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-id-card text-orange-500"></i>Pengajuan Seller FJB
                            </h3>
                            @php
                                $sc = match($user->seller_status) {
                                    'pending'  => 'bg-orange-100 text-orange-700',
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default    => 'bg-gray-100 text-gray-600',
                                };
                                $sl = match($user->seller_status) {
                                    'pending'  => 'Menunggu Review',
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    default    => $user->seller_status,
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $sc }}">{{ $sl }}</span>
                        </div>

                        <div class="p-6">
                            {{-- Foto KTP --}}
                            @if($user->seller_ktp)
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Foto KTP</p>
                                    <img src="{{ Storage::url($user->seller_ktp) }}" alt="KTP"
                                         class="rounded-lg border border-gray-200 max-h-64 object-cover">
                                </div>
                            @endif

                            @if($user->seller_status === 'rejected' && $user->seller_rejection_reason)
                                <div class="mb-4 p-3 bg-red-50 rounded-lg border border-red-200 text-sm text-red-700">
                                    <strong>Alasan penolakan:</strong> {{ $user->seller_rejection_reason }}
                                </div>
                            @endif

                            @if($user->seller_status === 'pending')
                                <div class="flex gap-3">
                                    {{-- Approve --}}
                                    <form method="POST" action="{{ route('admin.users.approveSeller', $user) }}" class="flex-1">
                                        @csrf @method('PATCH')
                                        <button type="submit" onclick="return confirm('Setujui pengajuan seller {{ $user->name }}?')"
                                                class="w-full py-2 px-4 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                                            <i class="fas fa-check mr-2"></i>Setujui
                                        </button>
                                    </form>

                                    {{-- Reject --}}
                                    <div class="flex-1" x-data="{ showReject: false }">
                                        <button @click="showReject = !showReject"
                                                class="w-full py-2 px-4 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm font-medium hover:bg-red-100 transition-colors">
                                            <i class="fas fa-times mr-2"></i>Tolak
                                        </button>
                                        <div x-show="showReject" x-transition class="mt-3">
                                            <form method="POST" action="{{ route('admin.users.rejectSeller', $user) }}">
                                                @csrf @method('PATCH')
                                                <textarea name="rejection_reason" rows="3" required
                                                          placeholder="Alasan penolakan (wajib diisi)..."
                                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent mb-2"></textarea>
                                                <button type="submit"
                                                        class="w-full py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                                                    Konfirmasi Tolak
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Approval Provider --}}
                @if($user->provider_status && $user->provider_status !== 'none')
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-user-tie text-blue-500"></i>Pengajuan Provider
                            </h3>
                            @php
                                $pc = match($user->provider_status) {
                                    'pending'  => 'bg-orange-100 text-orange-700',
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default    => 'bg-gray-100 text-gray-600',
                                };
                                $pl = match($user->provider_status) {
                                    'pending'  => 'Menunggu Review',
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    default    => $user->provider_status,
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $pc }}">{{ $pl }}</span>
                        </div>

                        <div class="p-6">
                            @if($user->provider_status === 'rejected' && $user->provider_rejection_reason)
                                <div class="mb-4 p-3 bg-red-50 rounded-lg border border-red-200 text-sm text-red-700">
                                    <strong>Alasan penolakan:</strong> {{ $user->provider_rejection_reason }}
                                </div>
                            @endif

                            @if($user->provider_status === 'pending')
                                <p class="text-sm text-gray-500 mb-4">
                                    Role provider: <strong>{{ $user->roles->whereIn('name', ['provider_residence', 'provider_event'])->pluck('display_name')->join(', ') }}</strong>
                                </p>
                                <div class="flex gap-3">
                                    <form method="POST" action="{{ route('admin.users.approveProvider', $user) }}" class="flex-1">
                                        @csrf @method('PATCH')
                                        <button type="submit" onclick="return confirm('Setujui pengajuan provider {{ $user->name }}?')"
                                                class="w-full py-2 px-4 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                                            <i class="fas fa-check mr-2"></i>Setujui
                                        </button>
                                    </form>
                                    <div class="flex-1" x-data="{ showReject: false }">
                                        <button @click="showReject = !showReject"
                                                class="w-full py-2 px-4 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm font-medium hover:bg-red-100 transition-colors">
                                            <i class="fas fa-times mr-2"></i>Tolak
                                        </button>
                                        <div x-show="showReject" x-transition class="mt-3">
                                            <form method="POST" action="{{ route('admin.users.rejectProvider', $user) }}">
                                                @csrf @method('PATCH')
                                                <textarea name="rejection_reason" rows="3" required
                                                          placeholder="Alasan penolakan..."
                                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent mb-2"></textarea>
                                                <button type="submit"
                                                        class="w-full py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                                                    Konfirmasi Tolak
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Jika tidak ada pengajuan --}}
                @if((!$user->seller_status || $user->seller_status === 'none') && (!$user->provider_status || $user->provider_status === 'none'))
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-400">
                        <i class="fas fa-clipboard-check text-4xl mb-3 opacity-30 block"></i>
                        <p class="text-sm">Tidak ada pengajuan aktif untuk pengguna ini</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
