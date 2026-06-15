<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private function orgId() { return auth()->user()->organization_id; }

    public function index()
    {
        $categories = Category::where('organization_id', $this->orgId())
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        return view('admin.merchant.categories.index', compact('categories'));
    }

    public function create() { return view('admin.merchant.categories.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        Category::create([
            'organization_id' => $this->orgId(),
            'name' => $request->name,
            'color' => $request->color ?? '#3B82F6',
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return redirect()->route('merchant.categories.index')->with('success', 'Kategoriya qo\'shildi!');
    }

    public function edit(Category $category)
    {
        if ($category->organization_id !== $this->orgId()) abort(403);
        return view('admin.merchant.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        if ($category->organization_id !== $this->orgId()) abort(403);
        $request->validate(['name' => 'required|string|max:100']);
        $category->update($request->only(['name', 'color', 'icon', 'sort_order', 'is_active']));
        return redirect()->route('merchant.categories.index')->with('success', 'Kategoriya yangilandi!');
    }

    public function destroy(Category $category)
    {
        if ($category->organization_id !== $this->orgId()) abort(403);
        $category->delete();
        return redirect()->route('merchant.categories.index')->with('success', 'Kategoriya o\'chirildi.');
    }
}
