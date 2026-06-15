<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Terminal;
use App\Models\Branch;
use Illuminate\Http\Request;

class TerminalController extends Controller
{
    private function orgId() { return auth()->user()->organization_id; }

    public function index() {
        $terminals = Terminal::where('organization_id', $this->orgId())->with(['branch', 'currentShift.user'])->get();
        return view('admin.merchant.terminals.index', compact('terminals'));
    }

    public function create() {
        $branches = Branch::where('organization_id', $this->orgId())->where('is_active', true)->get();
        return view('admin.merchant.terminals.create', compact('branches'));
    }

    public function store(Request $request) {
        $request->validate(['name' => 'required|string|max:100', 'branch_id' => 'required|exists:branches,id']);
        Terminal::create(array_merge($request->only(['name', 'branch_id', 'device_id']), [
            'organization_id' => $this->orgId(),
            'is_active' => $request->boolean('is_active', true),
        ]));
        return redirect()->route('merchant.terminals.index')->with('success', 'Terminal qo\'shildi!');
    }

    public function edit(Terminal $terminal) {
        if ($terminal->organization_id !== $this->orgId()) abort(403);
        $branches = Branch::where('organization_id', $this->orgId())->get();
        return view('admin.merchant.terminals.edit', compact('terminal', 'branches'));
    }

    public function update(Request $request, Terminal $terminal) {
        if ($terminal->organization_id !== $this->orgId()) abort(403);
        $terminal->update($request->only(['name', 'branch_id', 'device_id', 'is_active']));
        return redirect()->route('merchant.terminals.index')->with('success', 'Terminal yangilandi!');
    }

    public function destroy(Terminal $terminal) {
        if ($terminal->organization_id !== $this->orgId()) abort(403);
        $terminal->delete();
        return redirect()->route('merchant.terminals.index')->with('success', 'Terminal o\'chirildi.');
    }

    public function show(Terminal $terminal) {
        if ($terminal->organization_id !== $this->orgId()) abort(403);
        return view('admin.merchant.terminals.show', compact('terminal'));
    }
}
