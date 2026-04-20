<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filters
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function show(User $user)
    {
        $user->load(['roles', 'bookings.bookable', 'providedResidences', 'providedActivities']);

        // Get user activities
        $activities = UserActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.users.show', compact('user', 'activities'));
    }

    public function create()
    {
        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'role_id' => 'required|exists:roles,id',
            'profile_picture' => 'nullable|image|max:2048'
        ]);

        try {
            $userData = $request->except(['password', 'password_confirmation', 'role_id', 'profile_picture']);
            $userData['password'] = Hash::make($request->password);
            $userData['email_verified_at'] = now();

            if ($request->hasFile('profile_picture')) {
                $userData['profile_picture'] = $request->file('profile_picture')
                    ->store('profiles', 'public');
            }

            $user = User::create($userData);

            // Assign role
            $user->roles()->attach($request->role_id);

            return redirect()->route('admin.users.show', $user)
                ->with('success', 'User berhasil ditambahkan');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'role_id' => 'required|exists:roles,id',
            'profile_picture' => 'nullable|image|max:2048'
        ]);

        try {
            $userData = $request->except(['password', 'password_confirmation', 'role_id', 'profile_picture']);

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                $userData['profile_picture'] = $request->file('profile_picture')
                    ->store('profiles', 'public');
            }

            $user->update($userData);

            // Update role
            $user->roles()->sync([$request->role_id]);

            return redirect()->route('admin.users.show', $user)
                ->with('success', 'User berhasil diupdate');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate user: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(User $user)
    {
        // Prevent deleting admin users
        if ($user->hasRole('admin')) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus user admin');
        }

        // Check if user has active bookings
        $activeBookings = $user->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($activeBookings > 0) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus user dengan booking aktif');
        }

        try {
            // Delete profile picture
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    public function activities(User $user)
    {
        $activities = UserActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.users.activities', compact('user', 'activities'));
    }

    public function toggleStatus(User $user)
    {
        // Prevent disabling admin users
        if ($user->hasRole('admin')) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menonaktifkan user admin');
        }

        // Toggle user status (you might need to add is_active field to users table)
        $user->update([
            'is_active' => !($user->is_active ?? true)
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "User berhasil {$status}");
    }
}





















