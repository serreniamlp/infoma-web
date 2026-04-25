<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // FIX: filter role pakai role baru
        if ($request->filled('role')) {
            $role = $request->role;
            if ($role === 'seller') {
                $query->where('is_seller', true);
            } elseif ($role === 'pending_seller') {
                $query->where('seller_status', 'pending');
            } elseif ($role === 'pending_provider') {
                $query->where('provider_status', 'pending');
            } else {
                $query->whereHas('roles', fn($q) => $q->where('name', $role));
            }
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Hitung badge masing-masing tab
        $counts = [
            'all'              => User::count(),
            'user'             => User::whereHas('roles', fn($q) => $q->where('name', 'user'))->count(),
            'provider_residence' => User::whereHas('roles', fn($q) => $q->where('name', 'provider_residence'))->count(),
            'provider_event'   => User::whereHas('roles', fn($q) => $q->where('name', 'provider_event'))->count(),
            'seller'           => User::where('is_seller', true)->count(),
            'pending_seller'   => User::where('seller_status', 'pending')->count(),
            'pending_provider' => User::where('provider_status', 'pending')->count(),
        ];

        return view('admin.users.index', compact('users', 'counts'));
    }

    public function show(User $user)
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    // --- APPROVAL SELLER ---

    public function approveSeller(User $user)
    {
        if ($user->seller_status !== 'pending') {
            return redirect()->back()->with('error', 'Tidak ada pengajuan seller yang perlu disetujui.');
        }

        $user->update([
            'seller_status' => 'approved',
            'is_seller'     => true,
        ]);

        return redirect()->back()->with('success', "Pengajuan seller {$user->name} berhasil disetujui.");
    }

    public function rejectSeller(Request $request, User $user)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $user->update([
            'seller_status'            => 'rejected',
            'is_seller'                => false,
            'seller_rejection_reason'  => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', "Pengajuan seller {$user->name} ditolak.");
    }

    // --- APPROVAL PROVIDER ---

    public function approveProvider(User $user)
    {
        if ($user->provider_status !== 'pending') {
            return redirect()->back()->with('error', 'Tidak ada pengajuan provider yang perlu disetujui.');
        }

        $user->update(['provider_status' => 'approved']);

        return redirect()->back()->with('success', "Pengajuan provider {$user->name} berhasil disetujui.");
    }

    public function rejectProvider(Request $request, User $user)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $user->update([
            'provider_status'            => 'rejected',
            'provider_rejection_reason'  => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', "Pengajuan provider {$user->name} ditolak.");
    }

    public function activities(User $user)
    {
        // Riwayat aktivitas user di platform
        $bookings     = $user->bookings()->with('bookable')->orderBy('created_at', 'desc')->limit(10)->get();
        $transactions = $user->marketplaceTransactionsAsBuyer()->with('product')->orderBy('created_at', 'desc')->limit(10)->get();
        $products     = $user->marketplaceProducts()->orderBy('created_at', 'desc')->limit(10)->get();

        return view('admin.users.activities', compact('user', 'bookings', 'transactions', 'products'));
    }
}
