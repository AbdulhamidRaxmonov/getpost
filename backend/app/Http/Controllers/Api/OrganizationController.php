<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Organization::withCount(['users', 'branches', 'orders'])
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:organizations,phone',
            'admin_name' => 'required|string|max:255',
            'admin_phone' => 'required|string|unique:users,phone',
            'admin_password' => 'required|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            $organization = Organization::create([
                'name' => $request->name,
                'legal_name' => $request->legal_name,
                'tin' => $request->tin,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'subscription_plan' => $request->subscription_plan ?? 'basic',
                'subscription_expires_at' => $request->subscription_expires_at,
            ]);

            // Create main branch
            $branch = $organization->branches()->create([
                'name' => $request->name . ' (Asosiy)',
                'address' => $request->address,
                'phone' => $request->phone,
                'is_main' => true,
                'is_active' => true,
            ]);

            // Create admin user
            $adminUser = User::create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'name' => $request->admin_name,
                'phone' => $request->admin_phone,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'org_admin',
                'is_active' => true,
            ]);

            // Create default terminal
            $organization->terminals()->create([
                'branch_id' => $branch->id,
                'name' => 'Kassa-1',
                'is_active' => true,
            ]);

            // Create default units
            $units = [
                ['name' => 'Dona', 'short_name' => 'don', 'is_fractional' => false],
                ['name' => 'Kilogram', 'short_name' => 'kg', 'is_fractional' => true],
                ['name' => 'Litr', 'short_name' => 'l', 'is_fractional' => true],
                ['name' => 'Metr', 'short_name' => 'm', 'is_fractional' => true],
            ];
            foreach ($units as $unit) {
                $organization->units()->create($unit);
            }

            DB::commit();

            return response()->json([
                'organization' => $organization->load('branches'),
                'admin' => $adminUser,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Xatolik: ' . $e->getMessage()], 500);
        }
    }

    public function show(Organization $organization)
    {
        return response()->json($organization->load('branches', 'users', 'terminals'));
    }

    public function update(Request $request, Organization $organization)
    {
        $organization->update($request->only([
            'name', 'legal_name', 'tin', 'phone', 'email', 'address',
            'is_active', 'subscription_plan', 'subscription_expires_at',
        ]));

        return response()->json($organization);
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();
        return response()->json(['message' => 'Tashkilot o\'chirildi.']);
    }

    public function stats()
    {
        return response()->json([
            'total_organizations' => Organization::count(),
            'active_organizations' => Organization::where('is_active', true)->count(),
            'total_users' => User::count(),
            'total_orders_today' => Order::whereDate('created_at', today())->count(),
            'total_sales_today' => Order::whereDate('created_at', today())
                ->where('status', 'completed')->sum('total_amount'),
            'total_sales_month' => Order::where('created_at', '>=', now()->startOfMonth())
                ->where('status', 'completed')->sum('total_amount'),
        ]);
    }
}
