<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    private function orgId() { return auth()->user()->organization_id; }

    public function index() {
        $branches = Branch::where('organization_id', $this->orgId())->withCount(['terminals', 'users'])->get();
        return view('admin.merchant.branches.index', compact('branches'));
    }

    public function create() { return view('admin.merchant.branches.create'); }

    public function store(Request $request) {
        $request->validate(['name' => 'required|string|max:255']);
        Branch::create(array_merge($request->only(['name', 'address', 'phone']), [
            'organization_id' => $this->orgId(),
            'is_active' => $request->boolean('is_active', true),
        ]));
        return redirect()->route('merchant.branches.index')->with('success', 'Filial qo\'shildi!');
    }

    public function edit(Branch $branch) {
        if ($branch->organization_id !== $this->orgId()) abort(403);
        return view('admin.merchant.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch) {
        if ($branch->organization_id !== $this->orgId()) abort(403);
        $branch->update($request->only(['name', 'address', 'phone', 'is_active']));
        return redirect()->route('merchant.branches.index')->with('success', 'Filial yangilandi!');
    }

    public function destroy(Branch $branch) {
        if ($branch->organization_id !== $this->orgId()) abort(403);
        $branch->delete();
        return redirect()->route('merchant.branches.index')->with('success', 'Filial o\'chirildi.');
    }

    public function show(Branch $branch) {
        if ($branch->organization_id !== $this->orgId()) abort(403);
        return view('admin.merchant.branches.show', compact('branch'));
    }
}
