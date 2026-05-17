<?php
// app/Http/Controllers/Api/Auth/AuthController.php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login berhasil.',
            'data'    => [
                'user'  => new UserResource($user->load('roles')),
                'token' => $token,
            ],
        ]);
    }

    public function register(Request $request)
    {
        $isProvider = in_array($request->role, ['provider_residence', 'provider_event']);

        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:user,provider_residence,provider_event',
        ];

        // Provider wajib upload dokumen verifikasi
        if ($isProvider) {
            $rules['provider_nik']    = 'required|digits:16';
            $rules['provider_ktp']    = 'required|image|mimes:jpg,jpeg,png|max:2048';
            $rules['provider_selfie'] = 'required|string'; // base64
        }

        $request->validate($rules, [
            'email.unique'             => 'Email sudah terdaftar.',
            'password.confirmed'       => 'Konfirmasi password tidak cocok.',
            'provider_nik.required'    => 'NIK wajib diisi untuk provider.',
            'provider_nik.digits'      => 'NIK harus 16 digit.',
            'provider_ktp.required'    => 'Foto KTP wajib diunggah.',
            'provider_selfie.required' => 'Foto selfie wajib diambil.',
        ]);

        $userData = [
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'email_verified_at' => now(),
            'provider_status'   => $isProvider ? 'pending' : 'none',
        ];

        if ($isProvider) {
            $userData['provider_nik'] = $request->provider_nik;
            $userData['provider_ktp'] = $request->file('provider_ktp')
                ->store('provider-ktp', 'public');

            // Selfie base64
            $selfieBase64 = $request->provider_selfie;
            if (preg_match('/^data:image\/(\w+);base64,/', $selfieBase64, $matches)) {
                $imageData  = base64_decode(substr($selfieBase64, strpos($selfieBase64, ',') + 1));
                $extension  = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                $selfiePath = 'provider-selfie/' . uniqid('selfie_', true) . '.' . $extension;
                Storage::disk('public')->put($selfiePath, $imageData);
                $userData['provider_selfie'] = $selfiePath;
            } else {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Format foto selfie tidak valid.',
                ], 422);
            }
        }

        $user = User::create($userData);

        $role = Role::where('name', $request->role)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Registrasi berhasil.' . ($isProvider ? ' Akun sedang dalam proses verifikasi.' : ''),
            'data'    => [
                'user'  => new UserResource($user->load('roles')),
                'token' => $token,
            ],
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logout berhasil.',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data'   => [
                'user' => new UserResource($request->user()->load('roles')),
            ],
        ]);
    }

    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data'   => ['token' => $token],
        ]);
    }
}