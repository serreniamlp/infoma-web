<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone'    => ['required', 'string', 'max:20'],
            'address'  => ['required', 'string'],
            'role'     => ['required', 'in:user,provider_residence,provider_event'],
            'terms'    => ['required', 'accepted'],
        ]);

        $user = User::create([
            'name'               => $request->name,
            'email'              => $request->email,
            'password'           => Hash::make($request->password),
            'phone'              => $request->phone,
            'address'            => $request->address,
            'email_verified_at'  => now(),
        ]);

        // Assign role
        $role = Role::where('name', $request->role)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        // Login otomatis setelah register
        Auth::login($user);

        // Redirect berdasarkan role
        if ($user->hasRole('provider_residence')) {
            return redirect()->route('provider.residence.dashboard');
        } elseif ($user->hasRole('provider_event')) {
            return redirect()->route('provider.event.dashboard');
        } else {
            return redirect()->route('user.dashboard');
        }
    }
}