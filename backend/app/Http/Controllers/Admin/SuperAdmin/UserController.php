<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('organization')
            ->when($request->filled('search'), fn($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhere('phone', 'like', '%'.$request->search.'%'))
            ->when($request->filled('role'), fn($q) => $q->where('role', $request->role));

        $users = $query->latest()->paginate(20);
        return view('admin.super.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'phone' => 'required|unique:users,phone', 'password' => 'required|min:6']);
        User::create([
            'name' => $request->name, 'phone' => $request->phone, 'email' => $request->email,
            'password' => Hash::make($request->password), 'role' => $request->role ?? 'cashier',
            'organization_id' => $request->organization_id, 'is_active' => true,
        ]);
        return redirect()->route('super.users.index')->with('success', 'Foydalanuvchi qo\'shildi!');
    }

    public function create()
    {
        $organizations = Organization::all();
        return view('admin.super.users.create', compact('organizations'));
    }

    public function show(User $user) { return view('admin.super.users.show', compact('user')); }
    public function edit(User $user)
    {
        $organizations = Organization::all();
        return view('admin.super.users.edit', compact('user', 'organizations'));
    }

    public function update(Request $request, User $user)
    {
        $user->update($request->only(['name', 'email', 'role', 'is_active', 'organization_id']));
        if ($request->filled('password')) $user->update(['password' => Hash::make($request->password)]);
        return redirect()->route('super.users.index')->with('success', 'Yangilandi!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error', 'O\'zingizni o\'chira olmaysiz.');
        $user->delete();
        return redirect()->route('super.users.index')->with('success', 'O\'chirildi.');
    }
}
