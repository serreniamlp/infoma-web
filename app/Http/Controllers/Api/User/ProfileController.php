<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data'   => [
                'user' => new UserResource($request->user()->load('roles')),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'             => 'sometimes|string|max:255',
            'email'            => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:500',
            'profile_picture'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'current_password' => 'nullable|string',
            'password'         => 'nullable|string|min:8|confirmed',
        ], [
            'email.unique' => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'address']);

        // Handle foto profil
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')
                ->store('profile-pictures', 'public');
        }

        // Handle ganti password
        if ($request->filled('password')) {
            if (!$request->filled('current_password')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Password lama wajib diisi untuk mengganti password.',
                ], 422);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Password lama tidak sesuai.',
                ], 422);
            }

            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profil berhasil diperbarui.',
            'data'    => [
                'user' => new UserResource($user->fresh()->load('roles')),
            ],
        ]);
    }
}