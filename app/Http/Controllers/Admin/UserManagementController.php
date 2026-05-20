<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannedIdentity;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;

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

        if ($request->filled('role')) {
            $role = $request->role;
            if ($role === 'seller') {
                $query->where('is_seller', true);
            } elseif ($role === 'pending_seller') {
                $query->where('seller_status', 'pending');
            } elseif ($role === 'pending_provider') {
                $query->where('provider_status', 'pending');
            } elseif ($role === 'banned') {
                $query->where(function ($q) {
                    $q->where('ban_type', 'permanent')
                      ->orWhere(function ($q2) {
                          $q2->where('ban_type', 'temporary')
                             ->where('banned_until', '>', now());
                      });
                });
            } else {
                $query->whereHas('roles', fn($q) => $q->where('name', $role));
            }
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $counts = [
            'all'               => User::count(),
            'user'              => User::whereHas('roles', fn($q) => $q->where('name', 'user'))->count(),
            'provider_residence'=> User::whereHas('roles', fn($q) => $q->where('name', 'provider_residence'))->count(),
            'provider_event'    => User::whereHas('roles', fn($q) => $q->where('name', 'provider_event'))->count(),
            'seller'            => User::where('is_seller', true)->count(),
            'pending_seller'    => User::where('seller_status', 'pending')->count(),
            'pending_provider'  => User::where('provider_status', 'pending')->count(),
            'banned'            => User::where('ban_type', 'permanent')
                                       ->orWhere(fn($q) => $q->where('ban_type', 'temporary')->where('banned_until', '>', now()))
                                       ->count(),
        ];

        return view('admin.users.index', compact('users', 'counts'));
    }

    public function show(User $user)
    {
        $user->load('roles', 'bannedByAdmin');
        return view('admin.users.show', compact('user'));
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    // ── BAN METHODS (BARU) ──────────────────────────────────────────────

    /**
     * Ban user — temporary atau permanent.
     * POST /admin/users/{user}/ban
     */
    public function ban(Request $request, User $user)
    {
        if ($user->hasRole('admin')) {
            return redirect()->back()->with('error', 'Admin tidak bisa di-ban.');
        }

        $request->validate([
            'ban_type'    => 'required|in:temporary,permanent',
            'ban_reason'  => 'required|string|max:1000',
            'ban_duration'=> 'required_if:ban_type,temporary|nullable|integer|min:1|max:365',
            'ban_unit'    => 'required_if:ban_type,temporary|nullable|in:hours,days,weeks',
        ], [
            'ban_type.required'     => 'Tipe ban wajib dipilih.',
            'ban_reason.required'   => 'Alasan ban wajib diisi.',
            'ban_duration.required_if' => 'Durasi ban wajib diisi untuk ban sementara.',
            'ban_unit.required_if'  => 'Satuan waktu ban wajib dipilih.',
        ]);

        $bannedUntil = null;

        if ($request->ban_type === 'temporary') {
            $bannedUntil = match($request->ban_unit) {
                'hours' => now()->addHours($request->ban_duration),
                'weeks' => now()->addWeeks($request->ban_duration),
                default  => now()->addDays($request->ban_duration),
            };
        }

        // Update status ban user
        $user->update([
            'ban_type'    => $request->ban_type,
            'banned_until'=> $bannedUntil,
            'ban_reason'  => $request->ban_reason,
            'banned_by'   => Auth::id(),
            'banned_at'   => now(),
            'is_active'   => false,
        ]);

        // Jika permanen → masukkan email & NIK ke blacklist
        if ($request->ban_type === 'permanent') {
            BannedIdentity::banEmail($user->email, $request->ban_reason, Auth::id(), $user->id);

            // Masukkan NIK ke blacklist jika ada
            $nik = $user->seller_nik ?? $user->provider_nik;
            if ($nik) {
                BannedIdentity::banNik($nik, $request->ban_reason, Auth::id(), $user->id);
            }
        }

        // Revoke semua token Sanctum (paksa logout dari mobile)
        $user->tokens()->delete();

        // Kirim notifikasi ke user
        $this->sendBanNotification($user);

        $label = $request->ban_type === 'permanent'
            ? 'secara permanen'
            : "sementara hingga {$bannedUntil->format('d M Y, H:i')}";

        return redirect()->back()->with('success', "Akun {$user->name} berhasil di-ban {$label}.");
    }

    /**
     * Cabut ban user.
     * PATCH /admin/users/{user}/unban
     */
    public function unban(User $user)
    {
        if (!$user->isBanned()) {
            return redirect()->back()->with('error', 'User ini tidak sedang dalam status ban.');
        }

        $user->update([
            'ban_type'    => null,
            'banned_until'=> null,
            'ban_reason'  => null,
            'banned_by'   => null,
            'banned_at'   => null,
            'is_active'   => true,
        ]);

        // Kirim notifikasi unban
        NotificationService::send(
            $user->id,
            'akun.unban',
            'Akun kamu telah dipulihkan. Kamu sudah bisa menggunakan EduLiving kembali.',
            '/',
            'fa-check-circle',
            'green'
        );

        return redirect()->back()->with('success', "Ban akun {$user->name} berhasil dicabut.");
    }

    // ── APPROVAL SELLER ─────────────────────────────────────────────────

    public function approveSeller(User $user)
    {
        if ($user->seller_status !== 'pending') {
            return redirect()->back()->with('error', 'Tidak ada pengajuan seller yang perlu disetujui.');
        }

        $user->update([
            'seller_status' => 'approved',
            'is_seller'     => true,
        ]);

        NotificationService::sellerDisetujui($user->id);

        return redirect()->back()->with('success', "Pengajuan seller {$user->name} berhasil disetujui.");
    }

    public function rejectSeller(Request $request, User $user)
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $user->update([
            'seller_status'           => 'rejected',
            'is_seller'               => false,
            'seller_rejection_reason' => $request->rejection_reason,
        ]);

        NotificationService::sellerDitolak($user->id, $request->rejection_reason);

        return redirect()->back()->with('success', "Pengajuan seller {$user->name} ditolak.");
    }

    // ── APPROVAL PROVIDER ────────────────────────────────────────────────

    public function approveProvider(User $user)
    {
        if ($user->provider_status !== 'pending') {
            return redirect()->back()->with('error', 'Tidak ada pengajuan provider yang perlu disetujui.');
        }

        $user->update(['provider_status' => 'approved']);

        // Jika user daftar via role-switch (pending_role ada), assign role tersebut
        if ($user->pending_role) {
            $role = \App\Models\Role::where('name', $user->pending_role)->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
            $roleLabel = match($user->pending_role) {
                'provider_residence' => 'hunian',
                'provider_event'     => 'event',
                default              => $user->pending_role,
            };
            $user->update(['pending_role' => null]);
        } else {
            // Fallback: assign berdasarkan role existing
            $roleLabel = $user->roles
                ->whereIn('name', ['provider_residence', 'provider_event'])
                ->map(fn($r) => match($r->name) {
                    'provider_residence' => 'hunian',
                    'provider_event'     => 'event',
                    default              => $r->name,
                })->join(' & ');
        }

        NotificationService::providerDisetujui($user->id, $roleLabel);

        return redirect()->back()->with('success', "Pengajuan provider {$user->name} berhasil disetujui.");
    }

    public function rejectProvider(Request $request, User $user)
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $user->update([
            'provider_status'           => 'rejected',
            'provider_rejection_reason' => $request->rejection_reason,
        ]);

        $roleLabel = $user->roles
            ->whereIn('name', ['provider_residence', 'provider_event'])
            ->map(fn($r) => match($r->name) {
                'provider_residence' => 'hunian',
                'provider_event'     => 'event',
                default              => $r->name,
            })->join(' & ');

        NotificationService::providerDitolak($user->id, $roleLabel, $request->rejection_reason);

        return redirect()->back()->with('success', "Pengajuan provider {$user->name} ditolak.");
    }

    public function activities(User $user)
    {
        $bookings     = $user->bookings()->with('bookable')->orderBy('created_at', 'desc')->limit(10)->get();
        $transactions = $user->marketplaceTransactionsAsBuyer()->with('product')->orderBy('created_at', 'desc')->limit(10)->get();
        $products     = $user->marketplaceProducts()->orderBy('created_at', 'desc')->limit(10)->get();

        return view('admin.users.activities', compact('user', 'bookings', 'transactions', 'products'));
    }

    // ── PRIVATE HELPERS ──────────────────────────────────────────────────

    private function sendBanNotification(User $user): void
    {
        if ($user->isBannedPermanently()) {
            $message = 'Akun kamu telah dinonaktifkan secara permanen karena melanggar syarat & ketentuan EduLiving.'
                . ($user->ban_reason ? ' Alasan: ' . $user->ban_reason : '');
        } else {
            $message = 'Akun kamu ditangguhkan sementara hingga ' . $user->banned_until->format('d M Y, H:i') . '.'
                . ($user->ban_reason ? ' Alasan: ' . $user->ban_reason : '');
        }

        NotificationService::send(
            $user->id,
            'akun.banned',
            $message,
            '/',
            'fa-ban',
            'red'
        );
    }
}