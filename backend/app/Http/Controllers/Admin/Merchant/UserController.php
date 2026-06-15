<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function orgId() { return auth()->user()->organization_id; }

    public function index()
    {
        $users = User::where('organization_id', $this->orgId())
            ->with('branch')
            ->whereIn('role', ['org_admin', 'cashier'])
            ->get();
        return view('admin.merchant.users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::where('organization_id', $this->orgId())->get();
        return view('admin.merchant.users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => 'required|in:org_admin,cashier',
            'pin' => 'nullable|string|size:4|regex:/^[0-9]{4}$/',
        ]);

        User::create([
            'organization_id' => $this->orgId(),
            'branch_id' => $request->branch_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'pin' => $request->pin,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('merchant.users.index')->with('success', 'Hodim qo\'shildi!');
    }

    public function edit(User $user)
    {
        if ($user->organization_id !== $this->orgId()) abort(403);
        $branches = Branch::where('organization_id', $this->orgId())->get();
        return view('admin.merchant.users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->organization_id !== $this->orgId()) abort(403);
        $user->update($request->only(['name', 'email', 'branch_id', 'role', 'is_active']));
        if ($request->filled('password')) $user->update(['password' => Hash::make($request->password)]);
        return redirect()->route('merchant.users.index')->with('success', 'Hodim yangilandi!');
    }

    public function destroy(User $user)
    {
        if ($user->organization_id !== $this->orgId()) abort(403);
        if ($user->id === auth()->id()) return back()->with('error', 'O\'zingizni o\'chira olmaysiz.');
        $user->delete();
        return redirect()->route('merchant.users.index')->with('success', 'Hodim o\'chirildi.');
    }

    public function resetPin(Request $request, User $user)
    {
        if ($user->organization_id !== $this->orgId()) abort(403);
        $request->validate(['pin' => 'required|string|size:4|regex:/^[0-9]{4}$/']);
        $user->update(['pin' => $request->pin]);
        return back()->with('success', 'PIN kod yangilandi!');
    }

    public function show(User $user)
    {
        if ($user->organization_id !== $this->orgId()) abort(403);
        return view('admin.merchant.users.show', compact('user'));
    }
}
