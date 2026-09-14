<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            AuditLogger::log('login', 'User', Auth::id(), null, null, 'User logged into Solvia.Nova OS');
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', 'in:content_creator,designer,frontend_developer,backend_developer,iot_engineer,viewer'],
            'phone'     => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'phone'     => $data['phone'] ?? null,
            'status'    => 'active',
            'join_date' => now()->toDateString(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        AuditLogger::log('create', 'User', $user->id, null, ['role' => $user->role], "New user registered: {$user->name} ({$user->role})");

        return redirect()->route('dashboard')->with('success', "Welcome to Solvia.Nova OS, {$user->name}!");
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLogger::log('logout', 'User', Auth::id(), null, null, 'User logged out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
