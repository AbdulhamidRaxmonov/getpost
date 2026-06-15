<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\User;
use App\Models\Terminal;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::withCount(['users', 'branches', 'orders', 'products']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $organizations = $query->latest()->paginate(15);

        return view('admin.super.organizations.index', compact('organizations'));
    }

    public function create()
    {
        return view('admin.super.organizations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:organizations,phone',
            'admin_name' => 'required|string|max:255',
            'admin_phone' => 'required|string|unique:users,phone',
            'admin_password' => 'required|string|min:6',
            'subscription_plan' => 'required|in:basic,pro,enterprise',
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
                'subscription_plan' => $request->subscription_plan,
                'subscription_expires_at' => $request->subscription_expires_at,
                'is_active' => true,
            ]);

            $branch = Branch::create([
                'organization_id' => $organization->id,
                'name' => $organization->name . ' (Asosiy)',
                'address' => $request->address,
                'phone' => $request->phone,
                'is_main' => true,
                'is_active' => true,
            ]);

            User::create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'name' => $request->admin_name,
                'phone' => $request->admin_phone,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'pin' => $request->admin_pin ?? '1234',
                'role' => 'org_admin',
                'is_active' => true,
            ]);

            Terminal::create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'name' => 'Kassa-1',
                'is_active' => true,
            ]);

            $units = [
                ['name' => 'Dona', 'short_name' => 'don', 'is_fractional' => false],
                ['name' => 'Kilogram', 'short_name' => 'kg', 'is_fractional' => true],
                ['name' => 'Litr', 'short_name' => 'l', 'is_fractional' => true],
            ];
            foreach ($units as $unit) {
                Unit::create(array_merge($unit, ['organization_id' => $organization->id]));
            }

            DB::commit();

            return redirect()->route('super.organizations.index')
                ->with('success', "Tashkilot '{$organization->name}' muvaffaqiyatli yaratildi!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    public function show(Organization $organization)
    {
        $organization->load(['branches.terminals', 'users']);
        $stats = [
            'total_orders' => $organization->orders()->where('status', 'completed')->count(),
            'total_sales' => $organization->orders()->where('status', 'completed')->sum('total_amount'),
            'month_sales' => $organization->orders()->where('status', 'completed')
                ->where('created_at', '>=', now()->startOfMonth())->sum('total_amount'),
            'today_sales' => $organization->orders()->where('status', 'completed')
                ->whereDate('created_at', today())->sum('total_amount'),
            'products_count' => $organization->products()->count(),
        ];
        return view('admin.super.organizations.show', compact('organization', 'stats'));
    }

    public function edit(Organization $organization)
    {
        return view('admin.super.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subscription_plan' => 'required|in:basic,pro,enterprise',
        ]);

        $organization->update($request->only([
            'name', 'legal_name', 'tin', 'phone', 'email', 'address',
            'subscription_plan', 'subscription_expires_at', 'is_active'
        ]));

        return redirect()->route('super.organizations.show', $organization)
            ->with('success', 'Tashkilot yangilandi!');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();
        return redirect()->route('super.organizations.index')
            ->with('success', 'Tashkilot o\'chirildi.');
    }

    public function toggleStatus(Organization $organization)
    {
        $organization->update(['is_active' => !$organization->is_active]);
        $status = $organization->is_active ? 'faollashtirildi' : 'bloklandi';
        return back()->with('success', "Tashkilot {$status}.");
    }
}
