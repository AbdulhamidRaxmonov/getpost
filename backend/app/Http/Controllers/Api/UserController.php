<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('organization_id', $request->user()->organization_id);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        return response()->json($query->with('branch')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => 'required|in:org_admin,cashier',
            'pin' => 'nullable|string|size:4',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user = User::create([
            'organization_id' => $request->user()->organization_id,
            'branch_id' => $request->branch_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'pin' => $request->pin,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json($user, 201);
    }

    public function show(Request $request, User $user)
    {
        if ($user->organization_id !== $request->user()->organization_id) {
            abort(403);
        }
        return response()->json($user->load('branch'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->organization_id !== $request->user()->organization_id) {
            abort(403);
        }

        $user->update($request->only([
            'name', 'email', 'branch_id', 'role', 'is_active'
        ]));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json($user);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->organization_id !== $request->user()->organization_id) {
            abort(403);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'O\'zingizni o\'chira olmaysiz.'], 422);
        }

        $user->delete();
        return response()->json(['message' => 'Foydalanuvchi o\'chirildi.']);
    }

    public function resetPin(Request $request, User $user)
    {
        if ($user->organization_id !== $request->user()->organization_id) {
            abort(403);
        }

        $request->validate(['pin' => 'required|string|size:4']);

        $user->update(['pin' => $request->pin]);
        return response()->json(['message' => 'PIN kod yangilandi.']);
    }
}
