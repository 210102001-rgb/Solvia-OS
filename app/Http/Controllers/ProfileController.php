<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's personal profile edit form.
     * Accessible by ANY authenticated user regardless of role.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's personal profile.
     * Core Rule: The user's role CANNOT be changed from this action.
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
            'department' => 'nullable|string|max:100',
            'profile_bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',
            'avatar_url' => 'nullable|string|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        $old = $user->toArray();

        // 1. Update basic information
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->department = $data['department'] ?? null;
        $user->profile_bio = $data['profile_bio'] ?? null;

        // 2. Handle avatar upload or URL
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_url = Storage::url($path);
        } elseif ($request->filled('avatar_url')) {
            $user->avatar_url = $data['avatar_url'];
        }

        // 3. Handle optional password update with current password verification
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])->withInput();
            }
            $user->password = Hash::make($request->new_password);
        }

        // CRITICAL: The user's role is NEVER touched or modified here.
        // It strictly retains $user->role.
        $user->save();

        AuditLogger::log(
            'update',
            'User',
            $user->id,
            $old,
            $user->toArray(),
            "User {$user->name} ({$user->role}) updated their profile without changing role"
        );

        return back()->with('success', 'Profil berhasil diperbarui. Role Anda tetap terjaga sebagai ' . ucfirst(str_replace('_', ' ', $user->role)) . '.');
    }
}
