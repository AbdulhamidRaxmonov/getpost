<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Terminal;
use Illuminate\Http\Request;

class TerminalController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Terminal::where('organization_id', $request->user()->organization_id)
                ->with(['branch', 'currentShift'])
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $terminal = Terminal::create([
            'organization_id' => $request->user()->organization_id,
            'branch_id' => $request->branch_id,
            'name' => $request->name,
            'device_id' => $request->device_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json($terminal, 201);
    }

    public function show(Request $request, Terminal $terminal)
    {
        if ($terminal->organization_id !== $request->user()->organization_id) abort(403);
        return response()->json($terminal->load('branch', 'currentShift'));
    }

    public function update(Request $request, Terminal $terminal)
    {
        if ($terminal->organization_id !== $request->user()->organization_id) abort(403);
        $terminal->update($request->only(['name', 'branch_id', 'device_id', 'is_active']));
        return response()->json($terminal);
    }

    public function destroy(Request $request, Terminal $terminal)
    {
        if ($terminal->organization_id !== $request->user()->organization_id) abort(403);
        $terminal->delete();
        return response()->json(['message' => 'Terminal o\'chirildi.']);
    }
}
