<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Branch::where('organization_id', $request->user()->organization_id)
                ->with('terminals')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $branch = Branch::create([
            'organization_id' => $request->user()->organization_id,
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
            'is_main' => $request->boolean('is_main', false),
        ]);

        return response()->json($branch, 201);
    }

    public function show(Request $request, Branch $branch)
    {
        if ($branch->organization_id !== $request->user()->organization_id) abort(403);
        return response()->json($branch->load('terminals', 'users'));
    }

    public function update(Request $request, Branch $branch)
    {
        if ($branch->organization_id !== $request->user()->organization_id) abort(403);
        $branch->update($request->only(['name', 'address', 'phone', 'is_active', 'is_main']));
        return response()->json($branch);
    }

    public function destroy(Request $request, Branch $branch)
    {
        if ($branch->organization_id !== $request->user()->organization_id) abort(403);
        $branch->delete();
        return response()->json(['message' => 'Filial o\'chirildi.']);
    }
}
