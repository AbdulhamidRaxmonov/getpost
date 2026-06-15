<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Terminal;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Login with phone/email and password (for admin panels)
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)
            ->orWhere('email', $request->phone)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Telefon raqam yoki parol noto\'g\'ri.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Hisobingiz bloklangan.'], 403);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userResource($user),
        ]);
    }

    // PIN login for POS terminal
    public function pinLogin(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|integer',
            'pin' => 'required|string|min:4|max:6',
        ]);

        $terminal = Terminal::with('branch.organization')
            ->where('id', $request->terminal_id)
            ->where('is_active', true)
            ->first();

        if (!$terminal) {
            return response()->json(['message' => 'Terminal topilmadi.'], 404);
        }

        // Find cashier in this organization by PIN
        $user = User::where('organization_id', $terminal->organization_id)
            ->where('pin', $request->pin)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'PIN kod noto\'g\'ri.'], 401);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('pos_token', ['pos'])->plainTextToken;

        // Get current shift for terminal
        $currentShift = Shift::where('terminal_id', $terminal->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        return response()->json([
            'token' => $token,
            'user' => $this->userResource($user),
            'terminal' => [
                'id' => $terminal->id,
                'name' => $terminal->name,
                'branch' => [
                    'id' => $terminal->branch->id,
                    'name' => $terminal->branch->name,
                ],
                'organization' => [
                    'id' => $terminal->branch->organization->id,
                    'name' => $terminal->branch->organization->name,
                ],
            ],
            'current_shift' => $currentShift,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Chiqish muvaffaqiyatli.']);
    }

    public function me(Request $request)
    {
        return response()->json($this->userResource($request->user()->load('organization', 'branch')));
    }

    private function userResource(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'organization_id' => $user->organization_id,
            'branch_id' => $user->branch_id,
            'last_login_at' => $user->last_login_at,
        ];
    }
}
