<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ], [
            'phone.required' => 'Telefon raqamni kiriting.',
            'password.required' => 'Parolni kiriting.',
        ]);

        $user = User::where('phone', $request->phone)
            ->orWhere('email', $request->phone)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withInput()->withErrors([
                'phone' => 'Telefon raqam yoki parol noto\'g\'ri.',
            ]);
        }

        if (!$user->is_active) {
            return back()->withInput()->withErrors([
                'phone' => 'Hisobingiz bloklangan. Admin bilan bog\'laning.',
            ]);
        }

        if (!in_array($user->role, ['super_admin', 'org_admin'])) {
            return back()->withInput()->withErrors([
                'phone' => 'Bu panel uchun ruxsatingiz yo\'q.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_login_at' => now()]);

        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
