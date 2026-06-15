<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private function orgId() { return auth()->user()->organization_id; }

    public function index(Request $request)
    {
        $customers = Customer::where('organization_id', $this->orgId())
            ->when($request->filled('search'), fn($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhere('phone', 'like', '%'.$request->search.'%'))
            ->withCount('orders')
            ->paginate(20);
        return view('admin.merchant.customers.index', compact('customers'));
    }

    public function create() { return view('admin.merchant.customers.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Customer::create(array_merge($request->only(['name', 'phone', 'email', 'address', 'discount_percent']), [
            'organization_id' => $this->orgId(),
        ]));
        return redirect()->route('merchant.customers.index')->with('success', 'Mijoz qo\'shildi!');
    }

    public function show(Customer $customer)
    {
        if ($customer->organization_id !== $this->orgId()) abort(403);
        $customer->load(['orders' => fn($q) => $q->latest()->take(10)]);
        return view('admin.merchant.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        if ($customer->organization_id !== $this->orgId()) abort(403);
        return view('admin.merchant.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->organization_id !== $this->orgId()) abort(403);
        $customer->update($request->only(['name', 'phone', 'email', 'address', 'discount_percent', 'is_active']));
        return redirect()->route('merchant.customers.index')->with('success', 'Mijoz yangilandi!');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->organization_id !== $this->orgId()) abort(403);
        $customer->delete();
        return redirect()->route('merchant.customers.index')->with('success', 'Mijoz o\'chirildi.');
    }
}
