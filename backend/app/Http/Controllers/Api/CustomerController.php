<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::where('organization_id', $request->user()->organization_id)
            ->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return response()->json($query->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $customer = Customer::create([
            'organization_id' => $request->user()->organization_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'discount_percent' => $request->discount_percent ?? 0,
        ]);

        return response()->json($customer, 201);
    }

    public function show(Request $request, Customer $customer)
    {
        if ($customer->organization_id !== $request->user()->organization_id) {
            abort(403);
        }

        return response()->json($customer->load('orders'));
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->organization_id !== $request->user()->organization_id) {
            abort(403);
        }

        $customer->update($request->only([
            'name', 'phone', 'email', 'address', 'discount_percent', 'is_active'
        ]));

        return response()->json($customer);
    }

    public function destroy(Request $request, Customer $customer)
    {
        if ($customer->organization_id !== $request->user()->organization_id) {
            abort(403);
        }

        $customer->delete();
        return response()->json(['message' => 'Mijoz o\'chirildi.']);
    }
}
